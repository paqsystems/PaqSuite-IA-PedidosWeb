<?php

namespace App\Services\Auth;

use App\Exceptions\AuthFlowException;
use App\Models\PqPermiso;
use App\Models\User;
use App\Support\AuthErrorCodes;

final class SessionContextBuilder
{
    public function __construct(
        private readonly CommercialProfileResolver $commercialProfileResolver,
    ) {}

    public function build(User $user, ?string $token = null): array
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

        $commercialProfile = $this->commercialProfileResolver->resolveForUser($user);
        $cliente = $commercialProfile['cliente'];
        $vendedor = $commercialProfile['vendedor'];

        if ($cliente !== null && $vendedor !== null) {
            throw new AuthFlowException(
                AuthErrorCodes::noCommercialProfile,
                'auth.noCommercialProfile',
                403,
                'Usuario con perfil comercial ambiguo'
            );
        }

        $functionalProfile = null;
        $codCliente = null;
        $codVendedor = null;

        if ($cliente !== null) {
            $functionalProfile = 'cliente';
            $codCliente = (string) $cliente->cod_client;
        } elseif ($vendedor !== null) {
            $functionalProfile = $vendedor->supervisor ? 'supervisor' : 'vendedor';
            $codVendedor = (string) $vendedor->cod_vended;
        } else {
            throw new AuthFlowException(
                AuthErrorCodes::noCommercialProfile,
                'auth.noCommercialProfile',
                403
            );
        }

        $rol = $permiso->rol;
        $context = [
            'user' => [
                'id' => $user->id,
                'displayName' => (string) ($user->name_user ?? $user->codigo),
                'login' => (string) $user->codigo,
            ],
            'functionalProfile' => $functionalProfile,
            'codCliente' => $codCliente,
            'codVendedor' => $codVendedor,
            'locale' => (string) ($user->locale ?? 'es'),
            'theme' => (string) ($user->theme ?? 'generic.light'),
            'firstLogin' => (bool) $user->first_login,
            'security' => [
                'roles' => [(string) $rol->nombre_rol],
                'accesoTotal' => (bool) $rol->acceso_total,
            ],
        ];

        if ($token !== null) {
            $context['token'] = $token;
        }

        return $context;
    }
}
