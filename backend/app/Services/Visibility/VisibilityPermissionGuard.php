<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\PqPermiso;
use App\Models\PqRolAtributo;
use App\Models\User;
use App\Support\AuthErrorCodes;

final class VisibilityPermissionGuard
{
    public function ensureRepoPermission(User $user, string $procedimiento): void
    {
        $this->ensurePermission($user, $procedimiento, 'repo');
    }

    public function ensurePermission(User $user, string $procedimiento, string $tipoPermiso): void
    {
        $permiso = PqPermiso::query()
            ->with('rol')
            ->where('id_usuario', $user->id)
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'))
            ->first();

        if ($permiso === null || $permiso->rol === null) {
            throw new AuthFlowException(
                AuthErrorCodes::noPermission,
                'auth.noPermission',
                403
            );
        }

        if ((bool) $permiso->rol->acceso_total) {
            return;
        }

        $columnByTipoPermiso = [
            'alta' => 'permiso_alta',
            'modi' => 'permiso_modi',
            'baja' => 'permiso_baja',
            'repo' => 'permiso_repo',
        ];

        $permisoColumn = $columnByTipoPermiso[$tipoPermiso] ?? null;

        if ($permisoColumn === null) {
            throw new AuthFlowException(
                AuthErrorCodes::noPermission,
                'auth.noPermission',
                403
            );
        }

        $hasPermission = PqRolAtributo::query()
            ->where('id_rol', $permiso->rol->id)
            ->where('procedimiento', $procedimiento)
            ->where($permisoColumn, true)
            ->exists();

        if ($hasPermission) {
            return;
        }

        throw new AuthFlowException(
            AuthErrorCodes::noPermission,
            'auth.noPermission',
            403
        );
    }
}
