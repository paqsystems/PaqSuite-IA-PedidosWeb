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

        $hasRepoPermission = PqRolAtributo::query()
            ->where('id_rol', $permiso->rol->id)
            ->where('procedimiento', $procedimiento)
            ->where('permiso_repo', true)
            ->exists();

        if ($hasRepoPermission) {
            return;
        }

        throw new AuthFlowException(
            AuthErrorCodes::noPermission,
            'auth.noPermission',
            403
        );
    }
}
