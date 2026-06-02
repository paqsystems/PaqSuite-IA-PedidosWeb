<?php

namespace Tests\Feature\Api\PedidosWeb;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AuthenticatesPaqTenant;
use Tests\TestCase;

final class PedidosWebEndpointsAuthTest extends TestCase
{
    use AuthenticatesPaqTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatesPaqTenant();
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2?: array<string, mixed>}>
     */
    public static function protectedEndpointsProvider(): iterable
    {
        $sampleCabecera = [
            'cabecera' => ['cod_cliente' => 'CLIMVP001'],
            'renglones' => [
                [
                    'cod_articulo' => 'ART001',
                    'cantidad' => 1,
                    'precio' => 100,
                    'porc_bonif' => 0,
                    'porc_iva' => 21,
                ],
            ],
        ];

        yield 'comprobantes grabar' => ['POST', '/api/v1/comprobantes/grabar', [
            'accionGrabacion' => 'pedido',
            ...$sampleCabecera,
        ]];
        yield 'comprobantes copiar' => ['POST', '/api/v1/comprobantes/copiar', [
            'codComprobanteOrigen' => 'PED-1',
            'tipoDestino' => 'pedido',
        ]];
        yield 'pedidos store' => ['POST', '/api/v1/pedidos', $sampleCabecera];
        yield 'pedidos update' => ['PUT', '/api/v1/pedidos/PED-1', $sampleCabecera];
        yield 'pedidos show' => ['GET', '/api/v1/pedidos/PED-1'];
        yield 'pedidos destroy' => ['DELETE', '/api/v1/pedidos/PED-1'];
        yield 'pedidos edicion iniciar' => ['POST', '/api/v1/pedidos/PED-1/edicion/iniciar'];
        yield 'pedidos edicion actividad' => ['POST', '/api/v1/pedidos/PED-1/edicion/actividad'];
        yield 'pedidos edicion cancelar' => ['POST', '/api/v1/pedidos/PED-1/edicion/cancelar'];
        yield 'presupuestos store' => ['POST', '/api/v1/presupuestos', $sampleCabecera];
        yield 'presupuestos update' => ['PUT', '/api/v1/presupuestos/PRE-1', $sampleCabecera];
        yield 'presupuestos show' => ['GET', '/api/v1/presupuestos/PRE-1'];
        yield 'presupuestos cerrar' => ['POST', '/api/v1/presupuestos/PRE-1/cerrar', ['id_motivo' => -1]];
        yield 'motivos cierre' => ['GET', '/api/v1/motivos-cierre'];
        yield 'tratativas index' => ['GET', '/api/v1/presupuestos/PRE-1/tratativas'];
        yield 'tratativas store' => ['POST', '/api/v1/presupuestos/PRE-1/tratativas', ['observacion' => 'Test']];
        yield 'consultas pedidos ingresados' => ['GET', '/api/v1/consultas/pedidos-ingresados'];
        yield 'consultas pedidos pendientes' => ['GET', '/api/v1/consultas/pedidos-pendientes'];
        yield 'consultas presupuestos' => ['GET', '/api/v1/consultas/presupuestos'];
        yield 'consultas stock' => ['GET', '/api/v1/consultas/stock'];
        yield 'consultas deuda' => ['GET', '/api/v1/consultas/deuda'];
        yield 'consultas cheques' => ['GET', '/api/v1/consultas/cheques'];
        yield 'consultas historial ventas' => ['GET', '/api/v1/consultas/historial-ventas'];
        yield 'integracion logs' => ['GET', '/api/v1/integracion/logs'];
        yield 'dashboard operativo' => ['GET', '/api/v1/dashboard/operativo'];
    }

    #[Test]
    #[DataProvider('protectedEndpointsProvider')]
    public function endpointRequiresAuthentication(string $method, string $path, ?array $payload = null): void
    {
        $response = match ($method) {
            'POST' => $this->postJson($path, $payload ?? [], $this->tenantHeaders()),
            'PUT' => $this->putJson($path, $payload ?? [], $this->tenantHeaders()),
            'DELETE' => $this->deleteJson($path, $payload ?? [], $this->tenantHeaders()),
            default => $this->getJson($path, $this->tenantHeaders()),
        };

        $response->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    #[Test]
    public function comprobanteGrabarReturnsForbiddenWithoutAltaPermission(): void
    {
        if (! $this->ensureSeedSeguridadMvp()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para test 403.');
        }

        $permiso = \App\Models\PqPermiso::query()
            ->whereHas('user', fn ($query) => $query->where('codigo', 'vendedor.acotado.mvp'))
            ->firstOrFail();

        \App\Models\PqRolAtributo::query()
            ->where('id_rol', $permiso->id_rol)
            ->where('procedimiento', (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'))
            ->update(['permiso_alta' => false]);

        $payload = [
            'accionGrabacion' => 'pedido',
            'cabecera' => ['cod_cliente' => 'CLIMVP001'],
            'renglones' => [
                [
                    'cod_articulo' => 'ART001',
                    'cantidad' => 1,
                    'precio' => 100,
                    'porc_bonif' => 0,
                    'porc_iva' => 21,
                ],
            ],
        ];

        $this->postJson('/api/v1/comprobantes/grabar', $payload, $this->authHeadersFor('vendedor.acotado.mvp'))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    #[Test]
    public function dashboardOperativoReturnsForbiddenWithoutRepoPermission(): void
    {
        if (! $this->ensureSeedSeguridadMvp()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para test 403.');
        }

        $permiso = \App\Models\PqPermiso::query()
            ->whereHas('user', fn ($query) => $query->where('codigo', 'vendedor.acotado.mvp'))
            ->firstOrFail();

        \App\Models\PqRolAtributo::query()
            ->where('id_rol', $permiso->id_rol)
            ->where('procedimiento', (string) config('paqsuite_visibility.procedimientos.dashboard'))
            ->update(['permiso_repo' => false]);

        $this->getJson('/api/v1/dashboard/operativo', $this->authHeadersFor('vendedor.acotado.mvp'))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    private function ensureSeedSeguridadMvp(): bool
    {
        try {
            return $this->artisan('paqsuite:seed-seguridad-mvp')->run() === 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
