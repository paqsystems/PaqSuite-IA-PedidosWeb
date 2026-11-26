<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\PedidosWeb\IntegracionExportComprobantesFixture;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

/**
 * @group integracion
 */
final class IntegracionExportComprobantesDataTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function testFiltraPorEstadoRangoYDevuelveCabeceraYRenglon(): void
    {
        if (! $this->spDesplegado()) {
            $this->markTestSkipped('SP pq_sp_integracion_export_comprobantes_list no desplegado.');
        }

        $codigos = IntegracionExportComprobantesFixture::seedMultiEstado();
        $codPedidoEstado0 = $codigos[0];

        $response = $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=0:0&pageSize=50',
            $this->integrationHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('error', 0);

        $items = $response->json('resultado.items');
        $this->assertIsArray($items);
        $this->assertGreaterThanOrEqual(1, count($items));

        $match = collect($items)->firstWhere('cabecera.codPedido', $codPedidoEstado0);
        $this->assertNotNull($match);
        $this->assertSame(0, $match['cabecera']['estado']);
        $this->assertSame('integracion.erp.mvp', $match['cabecera']['codUsuarioWeb']);
        $this->assertArrayHasKey('renglones', $match);
        $this->assertCount(2, $match['renglones']);
        $this->assertArrayHasKey('cierre', $match);
        $this->assertNull($match['cierre']);
        $this->assertArrayNotHasKey('puedeEditar', $match['cabecera']);
        $this->assertArrayHasKey('codPedido', $match['cabecera']);
    }

    public function testCabeceraSinRenglonesYCierreEstado98(): void
    {
        if (! $this->spDesplegado()) {
            $this->markTestSkipped('SP pq_sp_integracion_export_comprobantes_list no desplegado.');
        }

        $codigos = IntegracionExportComprobantesFixture::seedMultiEstado();
        $codPedido98 = $codigos[98];
        $codPedido1 = $codigos[1];

        $cerrados = $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=98:98&codPedido='.$codPedido98,
            $this->integrationHeaders(),
        )->assertOk();

        $item98 = $cerrados->json('resultado.items.0');
        $this->assertNotNull($item98);
        $this->assertSame(98, $item98['cabecera']['estado']);
        $this->assertIsArray($item98['cierre']);

        $pendientes = $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=1:1&codPedido='.$codPedido1,
            $this->integrationHeaders(),
        )->assertOk();

        $item1 = $pendientes->json('resultado.items.0');
        $this->assertSame([], $item1['renglones']);
        $this->assertNull($item1['cierre']);
    }

    public function testFiltroCodCliente(): void
    {
        if (! $this->spDesplegado()) {
            $this->markTestSkipped('SP pq_sp_integracion_export_comprobantes_list no desplegado.');
        }

        IntegracionExportComprobantesFixture::seedMultiEstado();

        $response = $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=-1:99&codCliente='.IntegracionExportComprobantesFixture::CLIENTE_B,
            $this->integrationHeaders(),
        );

        $response->assertOk();
        $clientes = collect($response->json('resultado.items'))->pluck('cabecera.codCliente')->unique()->all();
        $this->assertSame([IntegracionExportComprobantesFixture::CLIENTE_B], $clientes);
    }

    public function testUnionOrMultiplesRangos(): void
    {
        if (! $this->spDesplegado()) {
            $this->markTestSkipped('SP pq_sp_integracion_export_comprobantes_list no desplegado.');
        }

        IntegracionExportComprobantesFixture::seedMultiEstado();

        $response = $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=-1:-1&estadoRangos=99:99',
            $this->integrationHeaders(),
        );

        $response->assertOk();
        $estados = collect($response->json('resultado.items'))->pluck('cabecera.estado')->unique()->sort()->values()->all();
        foreach ($estados as $estado) {
            $this->assertContains($estado, [-1, 99]);
        }
    }

    private function spDesplegado(): bool
    {
        if (! Schema::hasTable('pq_pedidosweb_pedidoscabecera')) {
            return false;
        }

        $row = DB::selectOne(
            "SELECT 1 AS ok FROM sys.procedures WHERE name = 'pq_sp_integracion_export_comprobantes_list'",
        );

        return $row !== null;
    }
}