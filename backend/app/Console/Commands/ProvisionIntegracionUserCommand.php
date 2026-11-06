<?php

namespace App\Console\Commands;

use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

final class ProvisionIntegracionUserCommand extends Command
{
    protected $signature = 'paqsuite:provision-integracion-user
                            {--codigo=integracion.erp : Código de usuario técnico}
                            {--email= : Email (default {codigo}@local.test)}
                            {--password= : Contraseña (default paqsuite_seed.mvpPassword)}';

    protected $description = 'Crea o actualiza usuario técnico con permiso pw_integracion_importacion (sin menú)';

    public function handle(): int
    {
        $codigo = (string) $this->option('codigo');
        $email = (string) ($this->option('email') ?: $codigo.'@local.test');
        $password = (string) ($this->option('password') ?: config('paqsuite_seed.mvpPassword'));
        $procedimiento = (string) config('paqsuite_visibility.procedimientos.integracionMaestros');
        $monoEmpresaId = (int) config('paqsuite_seed.monoEmpresaId', 0);

        if ($procedimiento === '') {
            $this->error('Falta config paqsuite_visibility.procedimientos.integracionMaestros');

            return self::FAILURE;
        }

        $rol = PqRol::query()->firstOrCreate(
            ['nombre_rol' => 'IntegracionErp'],
            ['descripcion_rol' => 'Usuario técnico importación ERP', 'acceso_total' => false],
        );

        PqRolAtributo::query()->updateOrCreate(
            ['id_rol' => $rol->id, 'procedimiento' => $procedimiento],
            [
                'permiso_alta' => true,
                'permiso_baja' => false,
                'permiso_modi' => false,
                'permiso_repo' => false,
            ],
        );

        $user = User::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'name_user' => 'Integración ERP',
                'email' => $email,
                'password_hash' => Hash::make($password),
                'activo' => true,
                'inhabilitado' => false,
                'first_login' => false,
                'locale' => 'es-AR',
                'theme' => 'light',
            ],
        );

        PqPermiso::query()->updateOrCreate(
            ['id_usuario' => $user->id],
            ['id_rol' => $rol->id, 'id_empresa' => $monoEmpresaId],
        );

        $token = $user->createToken('integracion-maestros')->plainTextToken;

        $this->info("Usuario: {$codigo}");
        $this->line("Token (mostrar una sola vez): {$token}");
        $this->line('Header: X-Paq-Cliente = tenant configurado en paqsuite_tenant.allowedClients');

        return self::SUCCESS;
    }
}
