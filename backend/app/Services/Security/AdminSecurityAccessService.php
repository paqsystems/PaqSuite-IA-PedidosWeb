<?php

namespace App\Services\Security;

use App\Exceptions\AuthFlowException;
use App\Models\User;
use App\Support\AuthErrorCodes;

final class AdminSecurityAccessService
{
    public function __construct(
        private readonly UserRoleUnionService $userRoleUnionService,
    ) {}

    public function ensure(User $user, string $procedimiento, string $tipoPermiso): void
    {
        if (! (bool) config('paqsuite_mvp.securityAdminEnabled')) {
            throw new AuthFlowException(
                AuthErrorCodes::notFound,
                'admin.security.notEnabled',
                404
            );
        }

        $union = $this->userRoleUnionService->resolveForUser($user);

        if ($union->hasAccesoTotal()) {
            return;
        }

        if ($union->hasPermission($procedimiento, $tipoPermiso)) {
            return;
        }

        throw new AuthFlowException(
            AuthErrorCodes::noPermission,
            'auth.noPermission',
            403
        );
    }
}
