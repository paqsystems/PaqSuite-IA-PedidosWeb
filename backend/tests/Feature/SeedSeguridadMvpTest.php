<?php

namespace Tests\Feature;

use App\Models\PqMenu;
use App\Models\PqPedidoswebCliente;
use App\Models\PqPedidoswebLogin;
use App\Models\PqPedidoswebVendedor;
use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Models\User;
use Tests\TestCase;

final class SeedSeguridadMvpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
    }

    public function testSeedSeguridadCreatesRolesUsersAndLinks(): void
    {
        $this->artisan('paqsuite:seed-seguridad-mvp')
            ->assertExitCode(0);

        $this->assertSame(4, PqRol::query()->whereIn('nombre_rol', [
            'Cliente', 'Vendedor', 'VendedorAcotado', 'Supervisor',
        ])->count());
        $this->assertSame(6, User::query()->whereIn('codigo', $this->mvpUserCodes())->count());
        $this->assertSame(5, PqPermiso::query()->whereIn('id_usuario', User::query()
            ->whereIn('codigo', [
                'cliente.mvp',
                'vendedor.acotado.mvp',
                'supervisor.mvp',
                'usuario.sinVinculo.mvp',
                'vendedor.sinMenu.mvp',
            ])
            ->pluck('id'))->count());
        $this->assertDatabaseHas('pq_pedidosweb_clientes', ['cod_client' => 'CLIMVP001', 'cod_login' => 'CLIMVP001']);
        $this->assertDatabaseHas('pq_pedidosweb_vendedores', ['cod_vended' => 'VENACOT01', 'cod_login' => 'VENACOT01']);
        $this->assertDatabaseHas('pq_pedidosweb_login', ['usuario' => 'cliente.mvp', 'cod_usuario_web' => 'CLIMVP001']);

        $sinPermiso = User::query()->where('codigo', 'usuario.sinPermiso.mvp')->firstOrFail();
        $this->assertNull(PqPermiso::query()->where('id_usuario', $sinPermiso->id)->first());

        $sinVinculo = User::query()->where('codigo', 'usuario.sinVinculo.mvp')->firstOrFail();
        $this->assertNotNull(PqPermiso::query()->where('id_usuario', $sinVinculo->id)->first());
        $this->assertSame(0, PqPedidoswebLogin::query()->where('usuario', $sinVinculo->codigo)->count());
        $this->assertSame(0, PqPedidoswebVendedor::query()->where('cod_login', $sinVinculo->codigo)->count());
        $this->assertSame(0, PqPedidoswebCliente::query()->where('cod_login', $sinVinculo->codigo)->count());
    }

    public function testSeedSeguridadIsIdempotent(): void
    {
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);

        $this->assertSame(4, PqRol::query()->whereIn('nombre_rol', [
            'Cliente', 'Vendedor', 'VendedorAcotado', 'Supervisor',
        ])->count());
        $this->assertSame(5, PqPermiso::query()->whereIn('id_usuario', User::query()
            ->whereIn('codigo', [
                'cliente.mvp',
                'vendedor.acotado.mvp',
                'supervisor.mvp',
                'usuario.sinVinculo.mvp',
                'vendedor.sinMenu.mvp',
            ])
            ->pluck('id'))->count());
    }

    public function testVendedorAcotadoHasMenuAttributes(): void
    {
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);

        $rolAcotado = PqRol::query()->where('nombre_rol', 'VendedorAcotado')->firstOrFail();

        $procedimientosRolAcotado = PqRolAtributo::query()
            ->where('id_rol', $rolAcotado->id)
            ->orderBy('procedimiento')
            ->pluck('procedimiento')
            ->all();

        $procedimientosEsperadosRolAcotado = array_values(array_unique(array_merge(
            config('paqsuite_mvp.visibilityProcedimientosByRole.VendedorAcotado', []),
            config('paqsuite_mvp.vendedorAcotadoProcedimientos', [])
        )));
        sort($procedimientosEsperadosRolAcotado);

        $this->assertSame($procedimientosEsperadosRolAcotado, $procedimientosRolAcotado);

        $rolVendedor = PqRol::query()->where('nombre_rol', 'Vendedor')->firstOrFail();

        $procedimientosRolVendedor = PqRolAtributo::query()
            ->where('id_rol', $rolVendedor->id)
            ->orderBy('procedimiento')
            ->pluck('procedimiento')
            ->all();

        $procedimientosEsperadosRolVendedor = config('paqsuite_mvp.visibilityProcedimientosByRole.Vendedor', []);
        sort($procedimientosEsperadosRolVendedor);

        $this->assertSame($procedimientosEsperadosRolVendedor, $procedimientosRolVendedor);
    }

    public function testSeedSeguridadFailsWithoutMenuPrerequisite(): void
    {
        $procedimientos = collect(config('paqsuite_mvp.menuItems'))->pluck('procedimiento');

        PqMenu::query()->whereIn('procedimiento', $procedimientos)->update(['enabled' => false]);

        $this->artisan('paqsuite:seed-seguridad-mvp')
            ->assertExitCode(1);
    }

    public function testSeedSeguridadFailsOnRoleConflict(): void
    {
        $supervisor = PqRol::query()->where('nombre_rol', 'Supervisor')->first();

        if ($supervisor === null) {
            PqRol::query()->create([
                'nombre_rol' => 'Supervisor',
                'descripcion_rol' => 'Conflicto',
                'acceso_total' => false,
            ]);
        } else {
            $supervisor->acceso_total = false;
            $supervisor->save();
        }

        $this->artisan('paqsuite:seed-seguridad-mvp')
            ->assertExitCode(1);
    }

    /**
     * @return list<string>
     */
    private function mvpUserCodes(): array
    {
        return [
            'cliente.mvp',
            'vendedor.acotado.mvp',
            'supervisor.mvp',
            'usuario.sinPermiso.mvp',
            'usuario.sinVinculo.mvp',
            'vendedor.sinMenu.mvp',
        ];
    }
}
