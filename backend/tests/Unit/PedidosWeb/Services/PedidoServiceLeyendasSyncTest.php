<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Models\PqPedidoswebCliente;
use App\Services\PedidosWeb\PedidoService;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

final class PedidoServiceLeyendasSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('paqsuite_pedidosweb.readFromErp', false);
    }

    #[Test]
    public function syncClienteLeyendasActualizaSoloLeyendasDirtyYHabilitadas(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_clientes no disponible.');
        }

        config()->set('paqsuite_pedidosweb.defaults.ClienteLeyenda1', 1);
        config()->set('paqsuite_pedidosweb.defaults.ClienteLeyenda2', 0);

        PqPedidoswebCliente::query()->where('cod_client', 'CLI-LEY-01')->delete();

        PqPedidoswebCliente::query()->create([
            'cod_client' => 'CLI-LEY-01',
            'nombre' => 'Cliente leyendas',
            'leyenda_1' => 'Entrega Folletería',
            'leyenda_2' => 'Leyenda 2 original',
        ]);

        $this->invokeSyncClienteLeyendasSiDirty(
            [
                'cod_cliente' => 'CLI-LEY-01',
                'leyenda_1' => 'Nueva leyenda 1',
                'leyenda_2' => 'Leyenda 2 editada',
            ],
            [
                'leyenda_1_dirty' => true,
                'leyenda_2_dirty' => true,
            ],
        );

        $cliente = PqPedidoswebCliente::query()->where('cod_client', 'CLI-LEY-01')->first();

        $this->assertNotNull($cliente);
        $this->assertSame('Nueva leyenda 1', $cliente->leyenda_1);
        $this->assertSame('Leyenda 2 original', $cliente->leyenda_2);
    }

    #[Test]
    public function syncClienteLeyendasNoActualizaSiNoHayDirty(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_clientes no disponible.');
        }

        config()->set('paqsuite_pedidosweb.defaults.ClienteLeyenda1', 1);

        PqPedidoswebCliente::query()->where('cod_client', 'CLI-LEY-02')->delete();

        PqPedidoswebCliente::query()->create([
            'cod_client' => 'CLI-LEY-02',
            'nombre' => 'Cliente sin dirty',
            'leyenda_1' => 'Valor original',
        ]);

        $this->invokeSyncClienteLeyendasSiDirty(
            [
                'cod_cliente' => 'CLI-LEY-02',
                'leyenda_1' => 'Valor modificado en sesión',
            ],
            [
                'leyenda_1_dirty' => false,
            ],
        );

        $cliente = PqPedidoswebCliente::query()->where('cod_client', 'CLI-LEY-02')->first();

        $this->assertNotNull($cliente);
        $this->assertSame('Valor original', $cliente->leyenda_1);
    }

    #[Test]
    public function syncClienteLeyendasRecortaValorLargoASesenta(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_clientes no disponible.');
        }

        config()->set('paqsuite_pedidosweb.defaults.ClienteLeyenda1', 1);

        PqPedidoswebCliente::query()->where('cod_client', 'CLI-LEY-60')->delete();

        PqPedidoswebCliente::query()->create([
            'cod_client' => 'CLI-LEY-60',
            'nombre' => 'Cliente recorte leyenda',
            'leyenda_1' => 'Corta',
        ]);

        $this->invokeSyncClienteLeyendasSiDirty(
            [
                'cod_cliente' => 'CLI-LEY-60',
                'leyenda_1' => str_repeat('d', 61),
            ],
            [
                'leyenda_1_dirty' => true,
            ],
        );

        $cliente = PqPedidoswebCliente::query()->where('cod_client', 'CLI-LEY-60')->first();

        $this->assertNotNull($cliente);
        $this->assertSame(str_repeat('d', 60), $cliente->leyenda_1);
        $this->assertSame(60, mb_strlen((string) $cliente->leyenda_1));
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  array<string, mixed>  $leyendasDirty
     */
    private function invokeSyncClienteLeyendasSiDirty(array $cabeceraPayload, array $leyendasDirty): void
    {
        $service = $this->app->make(PedidoService::class);
        $method = new ReflectionMethod(PedidoService::class, 'syncClienteLeyendasSiDirty');
        $method->setAccessible(true);
        $method->invoke($service, $cabeceraPayload, $leyendasDirty);
    }
}
