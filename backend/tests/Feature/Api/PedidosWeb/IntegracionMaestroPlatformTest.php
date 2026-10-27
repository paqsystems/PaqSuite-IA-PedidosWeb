<?php

namespace Tests\Feature\Api\PedidosWeb;

use App\Models\User;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PqPedidoswebLogIntegracion;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

/**
 * @group integracion
 */
final class IntegracionMaestroPlatformTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function testPostMaestrosSinTokenRetorna401(): void
    {
        $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteDelta([]), $this->tenantHeaders())
            ->assertStatus(401)
            ->assertJsonPath('error', 2002);
    }

    public function testVendedorAcotadoSinPermisoIntegracionRetorna403(): void
    {
        if (! Schema::hasTable('PQ_RolAtributo')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        $headers = $this->actingAsUserHeaders('vendedor.acotado.mvp');

        $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteDelta([]), $headers)
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'integracion.maestro.forbidden');
    }

    public function testTenantInvalidoRetorna400(): void
    {
        $headers = $this->integrationHeaders();
        $headers['X-Paq-Cliente'] = 'tenant-inexistente';

        $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteDelta([]), $headers)
            ->assertStatus(400)
            ->assertJsonPath('respuesta', 'tenant.invalid');
    }

    public function testDeltaVacioRetorna200NoOp(): void
    {
        $headers = $this->integrationHeaders();

        $response = $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteDelta([]), $headers);
        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.recibidos', 0)
            ->assertJsonPath('resultado.aplicados', 0);
    }

    public function testSnapshotVacioRetorna422(): void
    {
        $headers = $this->integrationHeaders();

        $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteSnapshot([]), $headers)
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.snapshotVacio');
    }

    public function testRecursoDesconocidoRetorna404(): void
    {
        $headers = $this->integrationHeaders();

        $this->postJson('/api/v1/integracion/maestros/no-existe', $this->loteDelta([]), $headers)
            ->assertStatus(404)
            ->assertJsonPath('respuesta', 'integracion.maestro.recursoDesconocido');
    }

    public function testModoSnapshotNoSoportadoRetorna422(): void
    {
        $headers = $this->integrationHeaders();

        $this->postJson(
            '/api/v1/integracion/maestros/listaprecios-articulos',
            $this->loteSnapshot([['codLista' => '1', 'codArticulo' => 'A']]),
            $headers,
        )
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.modoNoSoportado');
    }

    public function testLoteDemasiadoGrandeRetorna422(): void
    {
        $headers = $this->integrationHeaders();
        $items = array_fill(0, 1501, ['codProvin' => '01']);

        $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteDelta($items), $headers)
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.loteDemasiadoGrande');
    }

    public function testDeltaParcialConStubProvincias(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_logs_integracion')) {
            $this->markTestSkipped('Tabla logs integración no disponible.');
        }

        $headers = $this->integrationHeaders();

        $before = PqPedidoswebLogIntegracion::query()->count();

        $response = $this->postJson('/api/v1/integracion/maestros/provincias', $this->loteDelta([
            ['codProvin' => 'OK1'],
            ['nombrePro' => 'BAD sin clave'],
        ]), $headers);

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.recibidos', 2)
            ->assertJsonPath('resultado.aplicados', 1)
            ->assertJsonPath('resultado.rechazados', 1);

        $this->assertSame($before + 1, PqPedidoswebLogIntegracion::query()->count());
    }

    public function testIntegracionErpUserTienePermisoAlta(): void
    {
        if (! Schema::hasTable('PQ_RolAtributo')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        $user = User::query()->where('codigo', 'integracion.erp.mvp')->firstOrFail();
        $rolId = DB::table('Pq_Permiso')->where('id_usuario', $user->id)->value('id_rol');

        $atributo = DB::table('PQ_RolAtributo')
            ->where('id_rol', $rolId)
            ->where('procedimiento', 'pw_integracion_importacion')
            ->first();

        $this->assertNotNull($atributo);
        $this->assertTrue((bool) $atributo->permiso_alta);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function loteDelta(array $items): array
    {
        return [
            'modo' => 'delta',
            'fechaProceso' => '2026-10-21T12:00:00-03:00',
            'origen' => 'test',
            'items' => $items,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function loteSnapshot(array $items): array
    {
        return [
            'modo' => 'snapshot',
            'fechaProceso' => '2026-10-21T12:00:00-03:00',
            'origen' => 'test',
            'items' => $items,
        ];
    }
}
