<?php

namespace Tests\Feature;

use App\Models\PqPedidoswebCliente;
use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $this->ensureSeguridadMvpForVisibilityTests();

        $this->ensureComprobanteReferences();
        $this->ensureContactosTable();

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
        $this->revokeClientesRepoPermissionFor('cliente.mvp');

        $this->getJson('/api/v1/clientes', $this->authHeadersFor('cliente.mvp'))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    public function testClientesIncludesEmptyContactosWhenClienteHasNone(): void
    {
        $response = $this->getJson('/api/v1/clientes', $this->authHeadersFor('cliente.mvp'));

        $response->assertOk();

        $cliente = collect($response->json('resultado'))
            ->first(fn (array $item): bool => $item['codCliente'] === 'CLIMVP001');

        $this->assertIsArray($cliente);
        $this->assertSame([], $cliente['contactos']);
    }

    public function testClientesIncludesContactosOnlyForVisibleClients(): void
    {
        $this->upsertContacto('CLI-VEN-A', 'C02', 'Beta', '222', 'beta@paqsuite.local');
        $this->upsertContacto('CLI-VEN-A', 'C01', 'Ana', '111', 'ana@paqsuite.local');
        $this->upsertContacto('CLI-VEN-B', 'X01', 'Ajeno', '999', 'ajeno@paqsuite.local');

        $response = $this->getJson('/api/v1/clientes', $this->authHeadersFor('vendedor.acotado.mvp'));

        $response->assertOk();

        $clientes = collect($response->json('resultado'));
        $visible = $clientes->first(fn (array $item): bool => $item['codCliente'] === 'CLI-VEN-A');

        $this->assertIsArray($visible);
        $this->assertCount(2, $visible['contactos']);
        $this->assertSame('C01', $visible['contactos'][0]['codContacto']);
        $this->assertSame('Ana', $visible['contactos'][0]['nombre']);
        $this->assertSame('111', $visible['contactos'][0]['telefono']);
        $this->assertSame('ana@paqsuite.local', $visible['contactos'][0]['mail']);
        $this->assertFalse($clientes->contains(fn (array $item): bool => $item['codCliente'] === 'CLI-VEN-B'));
        $this->assertFalse(
            $clientes->contains(fn (array $item): bool => collect($item['contactos'] ?? [])
                ->contains(fn (array $contacto): bool => $contacto['codContacto'] === 'X01'))
        );
    }

    public function testShowClienteReturnsItemWithContactos(): void
    {
        $this->upsertContacto('CLI-VEN-A', 'C01', 'Ana', '111', 'ana@paqsuite.local');

        $response = $this->getJson('/api/v1/clientes/CLI-VEN-A', $this->authHeadersFor('vendedor.acotado.mvp'));

        $response->assertOk()
            ->assertJsonPath('resultado.codCliente', 'CLI-VEN-A')
            ->assertJsonPath('resultado.contactos.0.codContacto', 'C01')
            ->assertJsonPath('resultado.contactos.0.nombre', 'Ana');

        $this->assertIsInt($response->json('resultado.contactos.0.id'));
    }

    public function testShowClienteOutsideVisibleUniverseReturns404(): void
    {
        $this->upsertContacto('CLI-VEN-B', 'X01', 'Ajeno', '999', 'ajeno@paqsuite.local');

        $this->getJson('/api/v1/clientes/CLI-VEN-B', $this->authHeadersFor('vendedor.acotado.mvp'))
            ->assertStatus(404)
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    public function testShowClienteRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/clientes/CLI-VEN-A', $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    public function testShowClienteWithoutRepoPermissionReturns403(): void
    {
        $this->revokeClientesRepoPermissionFor('cliente.mvp');

        $this->getJson('/api/v1/clientes/CLIMVP001', $this->authHeadersFor('cliente.mvp'))
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

    private function revokeClientesRepoPermissionFor(string $codigo): void
    {
        $permisos = PqPermiso::query()
            ->with('rol')
            ->whereHas('user', fn ($query) => $query->where('codigo', $codigo))
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'))
            ->get();

        $this->assertFalse($permisos->isEmpty(), "Sin PqPermiso para {$codigo} en empresa MONO");

        foreach ($permisos as $permiso) {
            if ($permiso->rol === null) {
                continue;
            }

            PqRol::query()
                ->where('id', $permiso->rol->id)
                ->update(['acceso_total' => false]);

            PqRolAtributo::query()
                ->where('id_rol', $permiso->rol->id)
                ->where('procedimiento', (string) config('paqsuite_visibility.procedimientos.clientes'))
                ->delete();
        }
    }

    /**
     * Seed MVP puede fallar en Ankas si locale/theme de usuarios seed divergieron del catálogo.
     * Si el seed no cierra OK, se deja la password de test en transacción (rollback al terminar).
     */
    private function ensureSeguridadMvpForVisibilityTests(): void
    {
        if ($this->artisan('paqsuite:seed-seguridad-mvp')->run() === 0) {
            return;
        }

        $passwordHash = Hash::make($this->seedPassword);
        $updated = User::query()
            ->whereIn('codigo', ['cliente.mvp', 'vendedor.acotado.mvp'])
            ->update(['password_hash' => $passwordHash]);

        if ($updated < 2) {
            $this->fail('seed-seguridad-mvp falló y no están cliente.mvp / vendedor.acotado.mvp.');
        }
    }

    private function ensureContactosTable(): void
    {
        if (Schema::hasTable('pq_pedidosweb_clientescontactos')) {
            return;
        }

        Schema::create('pq_pedidosweb_clientescontactos', function ($table): void {
            $table->increments('id');
            $table->string('cod_client', 20);
            $table->string('cod_contacto', 50);
            $table->string('nombre', 120);
            $table->string('telefono', 50)->nullable();
            $table->string('mail', 120)->nullable();
            $table->unique(['cod_client', 'cod_contacto'], 'UQ_pw_clicont_cli_cod');
        });
    }

    private function upsertContacto(
        string $codCliente,
        string $codContacto,
        string $nombre,
        ?string $telefono,
        ?string $mail,
    ): void {
        DB::table('pq_pedidosweb_clientescontactos')->updateOrInsert(
            [
                'cod_client' => $codCliente,
                'cod_contacto' => $codContacto,
            ],
            [
                'nombre' => $nombre,
                'telefono' => $telefono,
                'mail' => $mail,
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
