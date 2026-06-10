<?php

namespace Tests\Feature;

use App\Models\PqPedidoswebCliente;
use App\Models\PqPermiso;
use App\Models\PqRolAtributo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class VisibilityDataTest extends TestCase
{
    private string $seedPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);

        $this->ensureComprobanteReferences();

        $this->upsertCliente('CLI-VEN-A', 'Cliente Vendedor A', 'VENACOT01');
        $this->upsertCliente('CLI-VEN-B', 'Cliente Vendedor B', 'VENSINM01');

        $this->upsertComprobante('PED-CLI-1', 'CLIMVP001', null, 0, 110.00);
        $this->upsertComprobante('PED-VEN-A-99', 'CLI-VEN-A', 'VENACOT01', 99, 200.00);
        $this->upsertComprobante('PED-VEN-A-0', 'CLI-VEN-A', 'VENACOT01', 0, 300.00);
        $this->upsertComprobante('PED-VEN-A-1', 'CLI-VEN-A', 'VENACOT01', 1, 150.00);
        $this->upsertComprobante('PED-VEN-B-0', 'CLI-VEN-B', 'VENSINM01', 0, 999.00);
    }

    public function testClientesForClienteReturnsOnlyOwnClient(): void
    {
        $response = $this->getJson('/api/v1/clientes', $this->authHeadersFor('cliente.mvp'));

        $response->assertOk()
            ->assertJsonFragment(['codCliente' => 'CLIMVP001']);

        $clientes = collect($response->json('resultado'));
        $this->assertTrue($clientes->contains(fn (array $cliente): bool => $cliente['codCliente'] === 'CLIMVP001'));
        $this->assertFalse($clientes->contains(fn (array $cliente): bool => $cliente['codCliente'] === 'CLI-VEN-A'));
    }

    public function testClientesForVendedorReturnsOnlyAssignedCustomers(): void
    {
        $response = $this->getJson('/api/v1/clientes', $this->authHeadersFor('vendedor.acotado.mvp'));

        $response->assertOk()
            ->assertJsonFragment(['codCliente' => 'CLI-VEN-A'])
            ->assertJsonMissing(['codCliente' => 'CLI-VEN-B']);
    }

    public function testComprobanteOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/comprobantes/PED-VEN-B-0', $this->authHeadersFor('vendedor.acotado.mvp'))
            ->assertStatus(404)
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    public function testDashboardResumenIsFilteredByVisibleClients(): void
    {
        $response = $this->getJson('/api/v1/dashboard/resumen', $this->authHeadersFor('vendedor.acotado.mvp'));

        $response
            ->assertOk()
            ->assertJsonPath('resultado.visibleClientsCount', 1)
            ->assertJsonPath('resultado.activeQuotesCount', 1)
            ->assertJsonPath('resultado.enteredOrdersCount', 1)
            ->assertJsonPath('resultado.pendingOrdersCount', 1)
            ->assertJsonPath('resultado.activeQuotesTotal', 200)
            ->assertJsonPath('resultado.enteredOrdersTotal', 300)
            ->assertJsonPath('resultado.pendingOrdersTotal', 150);

        $this->assertSame(200.0, (float) $response->json('resultado.activeQuotesTotal'));
        $this->assertSame(300.0, (float) $response->json('resultado.enteredOrdersTotal'));
        $this->assertSame(150.0, (float) $response->json('resultado.pendingOrdersTotal'));
    }

    public function testClientesRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/clientes', $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    public function testClientesWithoutRepoPermissionReturns403(): void
    {
        $permiso = PqPermiso::query()
            ->with('rol')
            ->whereHas('user', fn ($query) => $query->where('codigo', 'cliente.mvp'))
            ->firstOrFail();

        PqRolAtributo::query()
            ->where('id_rol', $permiso->rol->id)
            ->where('procedimiento', (string) config('paqsuite_visibility.procedimientos.clientes'))
            ->delete();

        $this->getJson('/api/v1/clientes', $this->authHeadersFor('cliente.mvp'))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    /**
     * @return array<string, string>
     */
    private function authHeadersFor(string $codigo): array
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'codigo' => $codigo,
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $loginResponse->assertOk();

        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.(string) $loginResponse->json('resultado.token'),
        ]);
    }

    private function upsertCliente(string $codCliente, string $nombre, string $codVendedor): void
    {
        PqPedidoswebCliente::query()->updateOrCreate(
            ['cod_client' => $codCliente],
            [
                'nombre' => $nombre,
                'fantasia' => $nombre,
                'cod_vended' => $codVendedor,
                'cod_login' => null,
                'e_mail' => strtolower($codCliente).'@paqsuite.local',
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'bonificacion' => 0,
                'nivel' => 0,
            ]
        );
    }

    private function upsertComprobante(
        string $codPedido,
        string $codCliente,
        ?string $codVendedor,
        int $estado,
        float $total,
    ): void {
        // SQL Server in this environment rejects the default stringified Carbon with milliseconds.
        $sqlServerDateTime = CarbonImmutable::now()->format('Ymd H:i:s');

        DB::table('pq_pedidosweb_pedidoscabecera')->updateOrInsert(
            ['cod_pedido' => $codPedido],
            [
                'cod_cliente' => $codCliente,
                'fecha' => $sqlServerDateTime,
                'nivel' => 0,
                'observaciones' => 'Comprobante seed visibilidad',
                'incluye_iva' => false,
                'moneda' => 1,
                'estado' => $estado,
                'tal_pedido_tango' => 1,
                'nro_pedido_tango' => $codPedido,
                'cod_usuario_web' => $codCliente,
                'fecha_modif' => $sqlServerDateTime,
                'total' => $total,
                'total_iva' => round($total * 0.21, 2),
                'descuento' => 0,
                'bonif_1' => 0,
                'bonif_2' => 0,
                'bonif_3' => 0,
                'cod_perfil' => 'MVP',
                'cod_vended' => $codVendedor,
                'cod_condvta' => 1,
                'id_de' => null,
                'cod_transpor' => 'MVP',
                'lista_precios' => 1,
            ]
        );
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

    /**
     * @return array<string, string>
     */
    private function tenantHeaders(): array
    {
        return [
            'X-Paq-Cliente' => 'desarrollo',
        ];
    }
}
