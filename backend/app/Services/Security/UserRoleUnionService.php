<?php

namespace App\Services\Security;

use App\Exceptions\AuthFlowException;
use App\Models\PqPermiso;
use App\Models\User;
use App\Support\AuthErrorCodes;

final class UserRoleUnionService
{
    public function resolveForUser(User $user): UserRoleUnion
    {
        $permisos = PqPermiso::query()
            ->with(['rol.atributos'])
            ->where('id_usuario', $user->id)
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'))
            ->get();

        $roles = $permisos
            ->pluck('rol')
            ->filter(static fn ($rol) => $rol !== null)
            ->unique(static fn ($rol) => (int) $rol->id)
            ->values();

        if ($roles->isEmpty()) {
            throw new AuthFlowException(
                AuthErrorCodes::noPermission,
                'auth.noPermission',
                403
            );
        }

        return new UserRoleUnion($roles);
    }

    public function tryResolveForUser(User $user): ?UserRoleUnion
    {
        try {
            return $this->resolveForUser($user);
        } catch (AuthFlowException) {
            return null;
        }
    }
}
