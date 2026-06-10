<?php

namespace Tests\Integration\PedidosWeb\Repositories;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Contracts\PedidosWeb\ClienteRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\TestCase;

final class PedidoRepositoryIntegrationTest extends TestCase
{
    use SeedsPedidosWebFeatureData;
    private PedidoRepositoryInterface $pedidoRepository;

    private PedidoDetalleRepositoryInterface $pedidoDetalleRepository;

    private ClienteRepositoryInterface $clienteRepository;

    private ArticuloRepositoryInterface $articuloRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pedidoRepository = $this->app->make(PedidoRepositoryInterface::class);
        $this->pedidoDetalleRepository = $this->app->make(PedidoDetalleRepositoryInterface::class);
        $this->clienteRepository = $this->app->make(ClienteRepositoryInterface::class);
        $this->articuloRepository = $this->app->make(ArticuloRepositoryInterface::class);

        if (! $this->bootstrapTenantData()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para integración de repositories.');
        }

        $this->ensureComprobanteReferences();
    }

    private function bootstrapTenantData(): bool
    {
        if ($this->artisan('paqsuite:seed-menus-mvp')->run() !== 0) {
            return false;
        }

        if ($this->artisan('paqsuite:seed-seguridad-mvp')->run() !== 0) {
            return false;
        }

        app(PedidosWebSchemaBootstrap::class)->ensureMvpSchema();

        return true;
    }

    public function testInsertCabeceraSyncDetalleAndFindWithDetalle(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PED-REPO-');

        $this->pedidoRepository->insertCabecera($this->cabeceraPayload($codPedido, 0));

        $this->pedidoDetalleRepository->syncDetalle($codPedido, [
            $this->renglonPayload(1, 'ART-REPO-A', 2, 100),
            $this->renglonPayload(2, 'ART-REPO-B', 1, 50),
        ]);

        $pedido = $this->pedidoRepository->findWithDetalle($codPedido);

        $this->assertNotNull($pedido);
        $this->assertSame($codPedido, $pedido->cod_pedido);
        $this->assertCount(2, $pedido->detalles);
        $this->assertSame(1, $pedido->detalles->first()->renglon);
    }

    public function testSyncDetalleReemplazaRenglones(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PED-SYNC-');

        $this->pedidoRepository->insertCabecera($this->cabeceraPayload($codPedido, 0));
        $this->pedidoDetalleRepository->syncDetalle($codPedido, [
            $this->renglonPayload(1, 'ART-1', 1, 10),
            $this->renglonPayload(2, 'ART-2', 1, 20),
            $this->renglonPayload(3, 'ART-3', 1, 30),
        ]);

        $this->pedidoDetalleRepository->syncDetalle($codPedido, [
            $this->renglonPayload(1, 'ART-NUEVO', 5, 99),
        ]);

        $detalles = $this->pedidoDetalleRepository->findByCodPedido($codPedido);

        $this->assertCount(1, $detalles);
        $this->assertSame('ART-NUEVO', $detalles->first()->cod_articulo);
    }

    public function testUpdateEstadoSinValidarNegocio(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PED-EST-');

        $this->pedidoRepository->insertCabecera($this->cabeceraPayload($codPedido, 1));

        $this->assertTrue($this->pedidoRepository->updateEstado($codPedido, 2));

        $pedido = $this->pedidoRepository->findByCodPedido($codPedido);
        $this->assertSame(2, $pedido?->estado);
    }

    public function testDeleteFisicoNoValidaEstado(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PED-DEL-');

        $this->pedidoRepository->insertCabecera($this->cabeceraPayload($codPedido, 1));
        $this->pedidoDetalleRepository->syncDetalle($codPedido, [
            $this->renglonPayload(1, 'ART-DEL', 1, 10),
        ]);

        $this->pedidoDetalleRepository->deleteByCodPedido($codPedido);
        $deletedCabecera = $this->pedidoRepository->deleteFisicoCabecera($codPedido);

        $this->assertSame(1, $deletedCabecera);
        $this->assertNull($this->pedidoRepository->findByCodPedido($codPedido));
    }

    public function testClienteRepositoryFindConDirecciones(): void
    {
        $codClient = 'CLI-REPO-'.substr(uniqid(), -6);

        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => $codClient],
            [
                'nombre' => 'Cliente Repo Test',
                'fantasia' => 'Cliente Repo Test',
                'cod_vended' => 'VENACOT01',
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'bonificacion' => 0,
                'nivel' => 0,
            ]
        );

        if (Schema::hasTable('pq_pedidosweb_clientesde')) {
            DB::table('pq_pedidosweb_clientesde')->updateOrInsert(
                ['cod_client' => $codClient, 'id_de' => 1],
                [
                    'cod_DE' => 'DE1',
                    'direccion' => 'Calle Test 123',
                    'localidad' => 'Localidad Test',
                    'c_postal' => '1000',
                    'cod_provin' => '01',
                    'habitual' => true,
                ]
            );
        }

        $cliente = $this->clienteRepository->findConDirecciones($codClient);

        $this->assertNotNull($cliente);
        $this->assertSame($codClient, $cliente->cod_client);

        if (Schema::hasTable('pq_pedidosweb_clientesde')) {
            $this->assertGreaterThanOrEqual(1, $cliente->direccionesEntrega->count());
        }
    }

    public function testArticuloRepositoryResuelvePrecioYStock(): void
    {
        $codArticulo = 'ART-REPO-'.substr(uniqid(), -5);

        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_articulos no disponible.');
        }

        DB::table('pq_pedidosweb_articulos')->updateOrInsert(
            ['codigo' => $codArticulo],
            [
                'descripcion' => 'Articulo repo test',
                'bonificacion' => 0,
                'usa_esc' => false,
                'porc_iva' => 21,
            ]
        );

        if (Schema::hasTable('pq_pedidosweb_stock')) {
            DB::table('pq_pedidosweb_stock')->updateOrInsert(
                ['cod_articulo' => $codArticulo],
                ['stock' => 100, 'comprometido' => 10]
            );
        }

        if (Schema::hasTable('pq_pedidosweb_listaprecios_articulos')) {
            DB::table('pq_pedidosweb_listaprecios_articulos')->updateOrInsert(
                ['cod_lista' => 1, 'cod_articulo' => $codArticulo],
                ['precio' => 150.50]
            );
        }

        $articulo = $this->articuloRepository->findByCodigo($codArticulo);
        $this->assertNotNull($articulo);

        $precio = $this->articuloRepository->findPrecioLista(1, $codArticulo);
        if (Schema::hasTable('pq_pedidosweb_listaprecios_articulos')) {
            $this->assertNotNull($precio);
            $this->assertEquals(150.50, (float) $precio->precio);
        }

        $stock = $this->articuloRepository->findStock($codArticulo);
        if (Schema::hasTable('pq_pedidosweb_stock')) {
            $this->assertNotNull($stock);
            $this->assertEquals(100, (float) $stock->stock);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function cabeceraPayload(string $codPedido, int $estado): array
    {
        $sqlServerDateTime = CarbonImmutable::now()->format('Ymd H:i:s');

        return [
            'cod_pedido' => $codPedido,
            'cod_cliente' => 'CLIMVP001',
            'fecha' => $sqlServerDateTime,
            'nivel' => 0,
            'observaciones' => 'Pedido repository test',
            'incluye_iva' => false,
            'moneda' => 1,
            'estado' => $estado,
            'tal_pedido_tango' => 1,
            'nro_pedido_tango' => substr($codPedido, 0, 20),
            'cod_usuario_web' => 'CLIMVP001',
            'fecha_modif' => $sqlServerDateTime,
            'total' => 100,
            'total_iva' => 21,
            'descuento' => 0,
            'bonif_1' => 0,
            'bonif_2' => 0,
            'bonif_3' => 0,
            'cod_perfil' => 'MVP',
            'cod_vended' => 'VENACOT01',
            'cod_condvta' => 1,
            'cod_transpor' => 'MVP',
            'lista_precios' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function renglonPayload(int $renglon, string $codArticulo, float $cantidad, float $precio): array
    {
        return [
            'renglon' => $renglon,
            'cod_articulo' => $codArticulo,
            'cantidad' => $cantidad,
            'porc_bonif' => 0,
            'precio' => $precio,
            'precio_neto' => $precio,
            'precio_bruto' => $precio,
            'porc_iva' => 21,
            'iva' => round($precio * $cantidad * 0.21, 2),
        ];
    }

    private function ensureComprobanteReferences(): void
    {
        if (Schema::hasTable('pq_pedidosweb_transportes')) {
            DB::table('pq_pedidosweb_transportes')->updateOrInsert(
                ['codigo' => 'MVP'],
                ['descripcion' => 'Transporte MVP']
            );
        }

        if (Schema::hasTable('pq_pedidosweb_perfil')) {
            DB::table('pq_pedidosweb_perfil')->updateOrInsert(
                ['cod_perfil' => 'MVP'],
                ['descripcion' => 'Perfil MVP']
            );
        }
    }
}
