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
        $this->postJson(
            '/api/v1/integracion/maestros/provincias',
            $this->importacionBatch('delta', []),
            $this->tenantHeaders(),
        )
            ->assertStatus(401)
            ->assertJsonPath('error', 2002);
    }

    public function testVendedorAcotadoSinPermisoIntegracionRetorna403(): void
    {
        if (! Schema::hasTable('PQ_RolAtributo')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        $headers = $this->actingAsUserHeaders('vendedor.acotado.mvp');

        $this->postJson(
            '/api/v1/integracion/maestros/provincias',
            $this->importacionBatch('delta', []),
            $headers,
        )
            ->assertStatus(403)
            ->assertJsonPath('respuesta', 'integracion.maestro.forbidden');
    }

    public function testTenantInvalidoRetorna400(): void
    {
        $headers = $this->integrationHeaders();
        $headers['X-Paq-Cliente'] = 'tenant-inexistente';

        $this->postJson(
            '/api/v1/integracion/maestros/provincias',
            $this->importacionBatch('delta', []),
            $headers,
        )
            ->assertStatus(400)
            ->assertJsonPath('respuesta', 'tenant.invalid');
    }

    public function testDeltaVacioRetorna202YProcesaSinItems(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_integracion_maestros_stage')) {
            $this->markTestSkipped('SP/staging de integración no desplegados.');
        }

        $headers = $this->integrationHeaders();
        $payload = $this->importacionBatch('delta', []);
        $receipt = $this->postImportacionAsync('/api/v1/integracion/maestros/provincias', $payload, $headers);
        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);

        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(0, $resultado['procesados'] ?? null);
    }

    public function testRecursoDesconocidoRetorna404(): void
    {
        $headers = $this->integrationHeaders();

        $this->postJson(
            '/api/v1/integracion/maestros/no-existe',
            $this->importacionBatch('delta', []),
            $headers,
        )
            ->assertStatus(404)
            ->assertJsonPath('respuesta', 'integracion.maestro.recursoDesconocido');
    }

    public function testLoteDemasiadoGrandeRetorna422(): void
    {
        $headers = $this->integrationHeaders();
        $items = array_fill(0, 1501, ['codProvin' => '01']);

        $this->postJson(
            '/api/v1/integracion/maestros/provincias',
            $this->importacionBatch('delta', $items),
            $headers,
        )
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.loteDemasiadoGrande');
    }

    public function testDeltaParcialConStubProvincias(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_integracion_maestros_stage')) {
            $this->markTestSkipped('SP/staging de integración no desplegados.');
        }

        $headers = $this->integrationHeaders();
        $payload = $this->importacionBatch('delta', [
            ['codProvin' => 'OK1'],
            ['nombrePro' => 'BAD sin clave'],
        ]);
        $receipt = $this->postImportacionAsync('/api/v1/integracion/maestros/provincias', $payload, $headers);
        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);

        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(2, $resultado['procesados'] ?? null);
        $this->assertSame(1, ($resultado['agregados'] ?? 0) + ($resultado['modificados'] ?? 0) + ($resultado['eliminados'] ?? 0));
        $this->assertSame(1, ($resultado['agregadosErroneos'] ?? 0) + ($resultado['modificadosErroneos'] ?? 0) + ($resultado['eliminadosErroneos'] ?? 0));
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
}
