<?php

namespace Tests\Feature\Api\ExcelImport;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ExcelImportFeatureTestCase;

final class PedidoMasivoExcelImportFeatureTest extends ExcelImportFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureSegundoClienteFixture();
    }

    public function testLoteFelizEntregaDosGrupos(): void
    {
        $lot = $this->createLotFromFile(
            $this->pedidoMasivoMultiGrupoFile(),
            'PEDIDO_MASIVO'
        );

        $this->assertSame('lista_para_procesar', $lot['estadoImportacion']);
        $this->assertSame(2, $lot['cantidadFilasValidas']);
        $this->assertSame(0, $lot['cantidadFilasConError']);

        $guid = (string) $lot['guidImportacion'];

        $this->actingAs($this->supervisorUser())
            ->postJson('/api/v1/excel-import/lotes/'.$guid.'/procesar', [], $this->tenantHeaders())
            ->assertOk();

        $response = $this->actingAs($this->supervisorUser())
            ->getJson('/api/v1/excel-import/lotes/'.$guid.'/filas/validas', $this->tenantHeaders())
            ->assertOk();

        $resultado = (array) $response->json('resultado');
        $this->assertCount(2, $resultado['items']);
        $this->assertArrayHasKey('grupos', $resultado);
        $this->assertCount(2, $resultado['grupos']);
        $this->assertSame('CLIMVP001', $resultado['grupos'][0]['clave']['codCliente']);
        $this->assertSame('CLIMVP002', $resultado['grupos'][1]['clave']['codCliente']);
    }

    public function testSinPermisoImportacionMasivaRetorna403(): void
    {
        $vendedor = User::query()->where('codigo', 'vendedor.acotado.mvp')->firstOrFail();

        $this->actingAs($vendedor)
            ->getJson('/api/v1/excel-import/procesos/PEDIDO_MASIVO/plantilla', $this->tenantHeaders())
            ->assertForbidden();
    }

    public function testCatalogoPedidoMasivoUsaHostImportacionMasiva(): void
    {
        $response = $this->actingAs($this->supervisorUser())
            ->getJson('/api/v1/excel-import/procesos/PEDIDO_MASIVO', $this->tenantHeaders())
            ->assertOk();

        $resultado = (array) $response->json('resultado');
        $this->assertSame('PEDIDO_MASIVO', $resultado['codigoProceso']);
        $this->assertFalse($resultado['permiteProcesamientoParcial']);
    }

    private function ensureSegundoClienteFixture(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return;
        }

        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => 'CLIMVP002'],
            [
                'nombre' => 'Cliente MVP 2',
                'fantasia' => 'Cliente MVP 2',
                'cod_vended' => 'VENACOT01',
                'cod_login' => 'CLIMVP002',
                'e_mail' => 'cliente2.mvp@paqsuite.local',
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'bonificacion' => 0,
                'nivel' => 0,
            ]
        );
    }
}
