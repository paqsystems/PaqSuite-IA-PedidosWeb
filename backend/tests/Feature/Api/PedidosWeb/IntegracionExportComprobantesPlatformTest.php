<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

final class IntegracionExportComprobantesPlatformTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function testGetSinTokenRetorna401(): void
    {
        $this->getJson('/api/v1/integracion/comprobantes?estadoRangos=0:0', $this->tenantHeaders())
            ->assertStatus(401)
            ->assertJsonPath('error', 2002);
    }

    public function testVendedorSinPermisoIntegracionRetorna403(): void
    {
        $headers = $this->actingAsUserHeaders('vendedor.acotado.mvp');

        $this->getJson('/api/v1/integracion/comprobantes?estadoRangos=0:0', $headers)
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'integracion.export.forbidden');
    }

    public function testSinEstadoRangosRetorna422(): void
    {
        $this->getJson('/api/v1/integracion/comprobantes', $this->integrationHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.export.estadoRangosRequerido');
    }

    public function testRangoInvalidoRetorna422(): void
    {
        $this->getJson('/api/v1/integracion/comprobantes?estadoRangos=1:0', $this->integrationHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.export.rangoInvalido');
    }

    public function testOkEnvelopeIntegracion(): void
    {
        if (! $this->spExportDesplegado()) {
            $this->markTestSkipped('SP pq_sp_integracion_export_comprobantes_list no desplegado.');
        }

        $response = $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=0:0&pageSize=10',
            $this->integrationHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'integracion.export.ok')
            ->assertJsonPath('resultado.page', 1)
            ->assertJsonPath('resultado.pageSize', 10)
            ->assertJsonStructure([
                'resultado' => [
                    'items',
                    'total',
                    'totalPages',
                    'metadata' => ['fechaProceso'],
                ],
            ]);
        $this->assertIsArray($response->json('resultado.items'));
        $this->assertGreaterThanOrEqual(0, (int) $response->json('resultado.total'));
    }

    public function testAliasPageSize(): void
    {
        if (! $this->spExportDesplegado()) {
            $this->markTestSkipped('SP pq_sp_integracion_export_comprobantes_list no desplegado.');
        }

        $this->getJson(
            '/api/v1/integracion/comprobantes?estadoRangos=0:0&page_size=25',
            $this->integrationHeaders(),
        )
            ->assertOk()
            ->assertJsonPath('resultado.pageSize', 25);
    }

    public function testRutaUsaThrottleIntegracionExport(): void
    {
        $ruta = collect(Route::getRoutes())->first(
            static fn ($route) => $route->getName() === 'api.v1.integracion.comprobantes.index',
        );

        $this->assertNotNull($ruta);
        $middleware = $ruta->gatherMiddleware();
        $this->assertTrue(
            in_array('throttle:integracion-export', $middleware, true)
            || collect($middleware)->contains(static fn (string $m) => str_starts_with($m, 'throttle:integracion-export')),
        );
    }

    private function spExportDesplegado(): bool
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