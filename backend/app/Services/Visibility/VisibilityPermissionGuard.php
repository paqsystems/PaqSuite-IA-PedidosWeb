<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\User;
use App\Services\Security\UserRoleUnionService;
use App\Support\AuthErrorCodes;

final class VisibilityPermissionGuard
{
    public function __construct(
        private readonly UserRoleUnionService $userRoleUnionService,
    ) {}

    public function ensureRepoPermission(User $user, string $procedimiento): void
    {
        $this->ensurePermission($user, $procedimiento, 'repo');
    }

    public function ensureAltaPermission(User $user, string $procedimiento): void
    {
        $this->ensurePermission($user, $procedimiento, 'alta');
    }

    public function ensureCargaComprobanteOrImportacionMasivaStore(User $user): void
    {
        $cargaComprobantes = (string) config('paqsuite_visibility.procedimientos.cargaComprobantes');
        $importacionMasiva = (string) config('paqsuite_visibility.procedimientos.importacionMasiva');

        if (
            $this->hasPermission($user, $cargaComprobantes, 'alta')
            || $this->hasPermission($user, $importacionMasiva, 'alta')
        ) {
            return;
        }

        throw new AuthFlowException(
            AuthErrorCodes::noPermission,
            'auth.noPermission',
            403
        );
    }

    public function hasPermission(User $user, string $procedimiento, string $tipoPermiso): bool
    {
        try {
            $this->ensurePermission($user, $procedimiento, $tipoPermiso);

            return true;
        } catch (AuthFlowException) {
            return false;
        }
    }

    public function ensurePermission(User $user, string $procedimiento, string $tipoPermiso): void
    {
        $union = $this->userRoleUnionService->resolveForUser($user);

        if ($union->hasAccesoTotal() || $union->hasPermission($procedimiento, $tipoPermiso)) {
            return;
        }

        throw new AuthFlowException(
            AuthErrorCodes::noPermission,
            'auth.noPermission',
            403
        );
    }
}
