<?php

namespace Database\Seeders\Mvp;

use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Models\User;
use App\Services\Seed\PedidoswebReferenceBootstrap;
use App\Services\Seed\SeedUpsertService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class SecurityMvpSeeder extends Seeder
{
    public function __construct(
        private readonly SeedUpsertService $seedUpsertService,
        private readonly PedidoswebReferenceBootstrap $pedidoswebReferenceBootstrap,
    ) {}

    public function run(): void
    {
        $passwordHash = Hash::make((string) config('paqsuite_seed.mvpPassword'));
        $monoEmpresaId = (int) config('paqsuite_seed.monoEmpresaId', 0);

        foreach (config('paqsuite_mvp.users', []) as $userSeed) {
            $user = $this->seedUpsertService->upsertByNaturalKey(
                new User(),
                ['codigo' => $userSeed['codigo']],
                [
                    'name_user' => $userSeed['name'],
                    'email' => $userSeed['email'],
                    'password_hash' => $passwordHash,
                    'activo' => $userSeed['activo'],
                    'inhabilitado' => $userSeed['inhabilitado'],
                    'first_login' => $userSeed['firstLogin'],
                    'locale' => $userSeed['locale'],
                    'theme' => $userSeed['theme'],
                    'menu_abrir_nueva_pestana' => $userSeed['openInNewTab'] ?? null,
                ],
                ['name_user', 'email', 'activo', 'inhabilitado', 'first_login', 'locale', 'theme', 'menu_abrir_nueva_pestana'],
            );

            if ($userSeed['hasPermiso'] && $userSeed['rol'] !== null) {
                $rol = PqRol::query()->where('nombre_rol', $userSeed['rol'])->firstOrFail();

                $this->seedUpsertService->upsertByNaturalKey(
                    new PqPermiso(),
                    ['id_usuario' => $user->id],
                    [
                        'id_rol' => $rol->id,
                        'id_empresa' => $monoEmpresaId,
                    ],
                    ['id_rol', 'id_empresa'],
                );

                $this->syncCommercialLink($user, $userSeed);
                $this->syncRolAtributos($rol, $userSeed);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $userSeed
     */
    private function syncCommercialLink(User $user, array $userSeed): void
    {
        if (! config('paqsuite_seed.syncCommercial')) {
            return;
        }

        if ($userSeed['codLogin'] === null || $userSeed['commercialTable'] === null) {
            return;
        }

        $this->pedidoswebReferenceBootstrap->ensureMvpReferences();

        $codLogin = (string) $userSeed['codLogin'];

        $this->seedUpsertService->upsertByNaturalKey(
            new \App\Models\PqPedidoswebLogin(),
            ['usuario' => $user->codigo],
            [
                'cod_usuario_web' => $codLogin,
                'password_bcrypt' => $user->password_hash,
                'primer_login' => (bool) $userSeed['firstLogin'],
                'tipo_cuenta' => $userSeed['commercialTable'] === 'cliente' ? 'C' : 'V',
                'cod_asociado' => $codLogin,
                'e_mail' => $userSeed['email'],
            ],
            ['cod_usuario_web', 'tipo_cuenta', 'cod_asociado', 'e_mail', 'primer_login'],
        );

        if ($userSeed['commercialTable'] === 'cliente') {
            $this->seedUpsertService->upsertByNaturalKey(
                new \App\Models\PqPedidoswebCliente(),
                ['cod_client' => $codLogin],
                [
                    'nombre' => $userSeed['name'],
                    'cod_login' => $codLogin,
                    'lista_precios' => 1,
                    'cod_condvta' => 1,
                    'bonificacion' => 0,
                    'nivel' => 0,
                ],
                ['nombre', 'cod_login'],
            );

            return;
        }

        $this->seedUpsertService->upsertByNaturalKey(
            new \App\Models\PqPedidoswebVendedor(),
            ['cod_vended' => $codLogin],
            [
                'nombre' => $userSeed['name'],
                'cod_login' => $codLogin,
                'supervisor' => (bool) $userSeed['supervisor'],
            ],
            ['nombre', 'cod_login', 'supervisor'],
        );
    }

    /**
     * @param  array<string, mixed>  $userSeed
     */
    private function syncRolAtributos(PqRol $rol, array $userSeed): void
    {
        $procedimientos = config('paqsuite_mvp.visibilityProcedimientosByRole.'.(string) $rol->nombre_rol, []);

        if (($userSeed['rolAtributos'] ?? null) === 'acotado') {
            $procedimientos = array_merge(
                $procedimientos,
                config('paqsuite_mvp.vendedorAcotadoProcedimientos', [])
            );
        }

        $procedimientos = array_values(array_unique(array_filter($procedimientos, 'is_string')));

        if ($procedimientos === []) {
            return;
        }

        foreach ($procedimientos as $procedimiento) {
            $this->seedUpsertService->upsertByNaturalKey(
                new PqRolAtributo(),
                [
                    'id_rol' => $rol->id,
                    'procedimiento' => $procedimiento,
                ],
                [
                    'permiso_alta' => false,
                    'permiso_baja' => false,
                    'permiso_modi' => false,
                    'permiso_repo' => true,
                ],
                ['permiso_alta', 'permiso_baja', 'permiso_modi', 'permiso_repo'],
            );
        }
    }
}
