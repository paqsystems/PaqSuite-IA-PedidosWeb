<?php

namespace Tests\Feature\Api\PedidosWeb;

use App\Models\User;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

/**
 * @group integracion
 */
final class IntegracionTransaccionPlatformTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function testPostTransaccionesSinTokenRetorna401(): void
    {
        $this->postJson(
            '/api/v1/integracion/transacciones/stock',
            $this->importacionBatch('delta', []),
            $this->tenantHeaders(),
        )
            ->assertStatus(401)
            ->assertJsonPath('error', 2002);
    }

    public function testVendedorSinPermisoIntegracionRetorna403(): void
    {
        if (! Schema::hasTable('PQ_RolAtributo')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        if (User::query()->where('codigo', 'vendedor.acotado.mvp')->doesntExist()) {
            $this->markTestSkipped('Usuario vendedor.acotado.mvp no disponible.');
        }

        $headers = $this->actingAsUserHeaders('vendedor.acotado.mvp');

        $this->postJson(
            '/api/v1/integracion/transacciones/stock',
            $this->importacionBatch('delta', []),
            $headers,
        )
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'integracion.maestro.forbidden');
    }

    public function testHistorialVentasSnapshotRetorna422(): void
    {
        $headers = $this->integrationHeaders();

        $this->postJson(
            '/api/v1/integracion/transacciones/historial-ventas',
            $this->importacionBatch('snapshot', []),
            $headers,
        )
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.modoNoSoportado');
    }

    public function testRecursoTransaccionDesconocidoRetorna404(): void
    {
        $headers = $this->integrationHeaders();

        $this->postJson(
            '/api/v1/integracion/transacciones/no-existe',
            $this->importacionBatch('delta', []),
            $headers,
        )
            ->assertStatus(404)
            ->assertJsonPath('respuesta', 'integracion.maestro.recursoDesconocido');
    }

    public function testDeltaVacioStockRetorna202(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_integracion_maestros_stage')
            && ! Schema::hasTable('PQ_GEN_IMPORTATIONS')) {
            $this->markTestSkipped('Motor GEN-36 no desplegado en BD.');
        }

        $headers = $this->integrationHeaders();
        $receipt = $this->postImportacionAsync(
            '/api/v1/integracion/transacciones/stock',
            $this->importacionBatch('delta', []),
            $headers,
        );
        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);

        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(0, $resultado['procesados'] ?? null);
    }

    public function testCuatroSlugsTransaccionRegistradosEnOpenApi(): void
    {
        $this->assertTrue(config('integracion_transacciones.resources.stock') !== null);
        $this->assertTrue(config('integracion_transacciones.resources.deuda') !== null);
        $this->assertTrue(config('integracion_transacciones.resources.cheques') !== null);
        $this->assertTrue(config('integracion_transacciones.resources.historial-ventas') !== null);
    }

    public function testIntegracionErpUserTienePermisoTransacciones(): void
    {
        if (! Schema::hasTable('PQ_RolAtributo')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        $user = User::query()->where('codigo', 'integracion.erp.mvp')->firstOrFail();
        $rolId = DB::table('Pq_Permiso')->where('id_usuario', $user->id)->value('id_rol');

        $permiso = DB::table('PQ_RolAtributo')
            ->where('id_rol', $rolId)
            ->where('procedimiento', 'pw_integracion_importacion')
            ->value('permiso_alta');

        $this->assertTrue((bool) $permiso);
    }

    public function testTenantInvalidoRetorna400(): void
    {
        $headers = $this->integrationHeaders();
        $headers['X-Paq-Cliente'] = 'tenant-inexistente';

        $this->postJson(
            '/api/v1/integracion/transacciones/stock',
            $this->importacionBatch('delta', []),
            $headers,
        )
            ->assertStatus(400)
            ->assertJsonPath('respuesta', 'tenant.invalid');
    }

    public function testLoteDemasiadoGrandeRetorna422(): void
    {
        $headers = $this->integrationHeaders();
        $items = array_fill(0, 1501, [
            'codArticulo' => 'ART-X',
            'stock' => 1,
            'comprometido' => 0,
        ]);

        $this->postJson(
            '/api/v1/integracion/transacciones/stock',
            $this->importacionBatch('delta', $items),
            $headers,
        )
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.loteDemasiadoGrande');
    }

    public function testCuatroRutasTransaccionRetornan403SinPermisoIntegracion(): void
    {
        if (! Schema::hasTable('PQ_RolAtributo')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        if (User::query()->where('codigo', 'vendedor.acotado.mvp')->doesntExist()) {
            $this->markTestSkipped('Usuario vendedor.acotado.mvp no disponible.');
        }

        $headers = $this->actingAsUserHeaders('vendedor.acotado.mvp');
        $payload = $this->importacionBatch('delta', []);

        foreach (['stock', 'deuda', 'cheques', 'historial-ventas'] as $recurso) {
            $this->postJson(
                '/api/v1/integracion/transacciones/'.$recurso,
                $payload,
                $headers,
            )
                ->assertStatus(403)
                ->assertJsonPath('respuesta', 'integracion.maestro.forbidden');
        }
    }

    public function testRecepcion202ExponeEnvelopeGen20(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $response = $this->postJson(
            '/api/v1/integracion/transacciones/deuda',
            $this->importacionBatch('delta', []),
            $headers,
        );

        $response->assertStatus(202)
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => ['importacionId', 'recurso', 'numeroLote', 'estado'],
            ]);
    }
}
