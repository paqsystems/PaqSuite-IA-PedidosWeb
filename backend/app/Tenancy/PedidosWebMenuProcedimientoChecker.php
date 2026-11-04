<?php

namespace App\Tenancy;

use App\Models\PqMenu;
use App\Models\User;
use App\Services\Security\UserRoleUnionService;
use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;

/**
 * Permisos de menú / capacidades GEN (emission.design → pw_diseniadorreportes).
 */
final class PedidosWebMenuProcedimientoChecker implements MenuProcedimientoChecker
{
    public function __construct(
        private readonly UserRoleUnionService $userRoleUnionService,
    ) {
    }

    public function existsInMenu(string $procedimiento): bool
    {
        $codigo = $this->resolveProcedimiento($procedimiento);
        if ($codigo === '') {
            return false;
        }

        return PqMenu::query()
            ->where('enabled', true)
            ->where('procedimiento', $codigo)
            ->exists();
    }

    public function userMayExecute(string $procedimiento): bool
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return false;
        }

        $codigo = $this->resolveProcedimiento($procedimiento);
        if ($codigo === '') {
            return false;
        }

        $union = $this->userRoleUnionService->resolveForUser($user);

        if ($union->hasAccesoTotal()) {
            return true;
        }

        return $union->hasPermission($codigo, 'repo');
    }

    private function resolveProcedimiento(string $procedimiento): string
    {
        $codigo = trim($procedimiento);

        return match ($codigo) {
            'emission.design' => 'pw_diseniadorreportes',
            default => $codigo,
        };
    }
}
