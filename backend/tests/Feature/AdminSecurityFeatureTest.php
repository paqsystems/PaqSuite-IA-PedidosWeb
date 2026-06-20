<?php

namespace Tests\Feature;

use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Models\User;
use Tests\TestCase;

final class AdminSecurityFeatureTest extends TestCase
{
    private string $seedPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');

        config(['paqsuite_mvp.securityAdminEnabled' => true]);

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);
    }

    public function testAdminRoutesReturn404WhenFlagDisabled(): void
    {
        config(['paqsuite_mvp.securityAdminEnabled' => false]);

        $this->getJson('/api/v1/admin/roles', $this->authHeadersFor('supervisor.mvp'))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'admin.security.notEnabled');
    }

    public function testSupervisorCanListAndCreateRoles(): void
    {
        $list = $this->getJson('/api/v1/admin/roles', $this->authHeadersFor('supervisor.mvp'));

        $list->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure(['resultado' => ['items']]);

        $create = $this->postJson('/api/v1/admin/roles', [
            'nombreRol' => 'RolD1Test',
            'descripcionRol' => 'Rol de prueba D1',
            'accesoTotal' => false,
        ], $this->authHeadersFor('supervisor.mvp'));

        $create->assertCreated()
            ->assertJsonPath('resultado.nombreRol', 'RolD1Test')
            ->assertJsonPath('resultado.enUso', false);

        $roleId = (int) $create->json('resultado.id');

        $this->putJson("/api/v1/admin/roles/{$roleId}", [
            'descripcionRol' => 'Actualizado',
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonPath('resultado.descripcionRol', 'Actualizado');

        $this->deleteJson("/api/v1/admin/roles/{$roleId}", [], $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonPath('error', 0);
    }

    public function testCannotDeleteRoleInUse(): void
    {
        $rolSupervisor = PqRol::query()->where('nombre_rol', 'Supervisor')->firstOrFail();

        $this->deleteJson("/api/v1/admin/roles/{$rolSupervisor->id}", [], $this->authHeadersFor('supervisor.mvp'))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'admin.roles.deleteInUse');
    }

    public function testDuplicateRoleNameReturns422(): void
    {
        $this->postJson('/api/v1/admin/roles', [
            'nombreRol' => 'Supervisor',
            'descripcionRol' => 'Duplicado',
            'accesoTotal' => false,
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'admin.roles.duplicateRoleName');
    }

    public function testRoleAttributesReadOnlyForAccesoTotal(): void
    {
        $rolSupervisor = PqRol::query()->where('nombre_rol', 'Supervisor')->firstOrFail();

        $this->getJson("/api/v1/admin/roles/{$rolSupervisor->id}/atributos", $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonPath('resultado.readOnly', true);

        $this->putJson("/api/v1/admin/roles/{$rolSupervisor->id}/atributos", [
            'items' => [],
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'admin.roles.atributosAccesoTotalReadOnly');
    }

    public function testRoleAttributesCanBeSyncedForNonAccesoTotalRole(): void
    {
        $rolAcotado = PqRol::query()->where('nombre_rol', 'VendedorAcotado')->firstOrFail();

        $get = $this->getJson("/api/v1/admin/roles/{$rolAcotado->id}/atributos", $this->authHeadersFor('supervisor.mvp'));

        $get->assertOk()
            ->assertJsonPath('resultado.readOnly', false);

        $firstItem = (array) ($get->json('resultado.items.0') ?? []);
        $this->assertNotEmpty($firstItem);

        $this->putJson("/api/v1/admin/roles/{$rolAcotado->id}/atributos", [
            'items' => [[
                'procedimiento' => $firstItem['procedimiento'],
                'permisoAlta' => true,
                'permisoBaja' => false,
                'permisoModi' => true,
                'permisoRepo' => true,
            ]],
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonStructure(['resultado' => ['actualizados', 'eliminados']]);
    }

    public function testRoleAttributesFullSyncPreservesOutOfCatalogAndMenuPermissions(): void
    {
        $rolAcotado = PqRol::query()->where('nombre_rol', 'VendedorAcotado')->firstOrFail();

        $get = $this->getJson("/api/v1/admin/roles/{$rolAcotado->id}/atributos", $this->authHeadersFor('supervisor.mvp'));
        $get->assertOk();

        $items = (array) ($get->json('resultado.items') ?? []);
        $this->assertNotEmpty($items);

        $beforeCount = PqRolAtributo::query()->where('id_rol', $rolAcotado->id)->count();
        $this->assertGreaterThanOrEqual(4, $beforeCount);

        $this->putJson("/api/v1/admin/roles/{$rolAcotado->id}/atributos", [
            'items' => $items,
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertOk();

        $afterCount = PqRolAtributo::query()->where('id_rol', $rolAcotado->id)->count();
        $this->assertSame($beforeCount, $afterCount);

        $this->assertTrue(
            PqRolAtributo::query()
                ->where('id_rol', $rolAcotado->id)
                ->where('procedimiento', 'pw_clientes_visibles')
                ->where('permiso_repo', true)
                ->exists()
        );

        $menu = $this->getJson('/api/v1/user/menu', $this->authHeadersFor('vendedor.acotado.mvp'));
        $menu->assertOk();

        $rootProcedimientos = array_map(
            static fn (array $item): string => (string) ($item['procedimiento'] ?? ''),
            (array) $menu->json('resultado')
        );

        $this->assertContains('grp_pedidos', $rootProcedimientos);
        $this->assertContains('pw_dashboard', $rootProcedimientos);
    }

    public function testPermisosCrudAndLookup(): void
    {
        $usuario = User::query()->where('codigo', 'usuario.sinPermiso.mvp')->firstOrFail();
        $rolCliente = PqRol::query()->where('nombre_rol', 'Cliente')->firstOrFail();

        $this->getJson('/api/v1/admin/permisos', $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonStructure(['resultado' => ['items']]);

        $create = $this->postJson('/api/v1/admin/permisos', [
            'idUsuario' => $usuario->id,
            'idRol' => $rolCliente->id,
        ], $this->authHeadersFor('supervisor.mvp'));

        $create->assertCreated();
        $permisoId = (int) $create->json('resultado.id');

        $this->postJson('/api/v1/admin/permisos', [
            'idUsuario' => $usuario->id,
            'idRol' => $rolCliente->id,
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'admin.permisos.duplicateAssignment');

        $rolVendedor = PqRol::query()->where('nombre_rol', 'Vendedor')->firstOrFail();

        $this->putJson("/api/v1/admin/permisos/{$permisoId}", [
            'idRol' => $rolVendedor->id,
        ], $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonPath('resultado.idRol', $rolVendedor->id);

        $this->getJson('/api/v1/admin/usuarios?search=sinPermiso', $this->authHeadersFor('supervisor.mvp'))
            ->assertOk()
            ->assertJsonStructure(['resultado' => ['items', 'page', 'page_size', 'total', 'total_pages']]);

        $this->deleteJson("/api/v1/admin/permisos/{$permisoId}", [], $this->authHeadersFor('supervisor.mvp'))
            ->assertOk();
    }

    public function testPermisoBatchCreatesAndSkipsDuplicates(): void
    {
        $usuario = User::query()->where('codigo', 'vendedor.acotado.mvp')->firstOrFail();
        $rolSupervisor = PqRol::query()->where('nombre_rol', 'Supervisor')->firstOrFail();
        $rolCliente = PqRol::query()->where('nombre_rol', 'Cliente')->firstOrFail();

        $response = $this->postJson('/api/v1/admin/permisos/batch', [
            'mode' => 'by_user',
            'anchorId' => $usuario->id,
            'rolIds' => [$rolSupervisor->id, $rolCliente->id, $rolSupervisor->id],
        ], $this->authHeadersFor('supervisor.mvp'));

        $response->assertOk()
            ->assertJsonStructure(['resultado' => ['creados', 'omitidos']]);

        $creados = (int) $response->json('resultado.creados');
        $omitidos = (int) $response->json('resultado.omitidos');

        $this->assertGreaterThanOrEqual(1, $creados);
        $this->assertGreaterThanOrEqual(0, $omitidos);

        PqPermiso::query()
            ->where('id_usuario', $usuario->id)
            ->whereIn('id_rol', [$rolSupervisor->id, $rolCliente->id])
            ->delete();
    }

    public function testVendedorAcotadoCannotAccessAdminRoles(): void
    {
        $this->getJson('/api/v1/admin/roles', $this->authHeadersFor('vendedor.acotado.mvp'))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    /**
     * @return array<string, string>
     */
    private function authHeadersFor(string $codigo): array
    {
        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$this->loginTokenFor($codigo),
        ]);
    }

    private function loginTokenFor(string $codigo): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => $codigo,
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk();

        return (string) $response->json('resultado.token');
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
