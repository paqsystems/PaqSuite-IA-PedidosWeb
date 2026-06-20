<?php

namespace App\Services\Auth;

use App\Exceptions\AuthFlowException;
use App\Models\User;
use App\Services\Security\UserRoleUnionService;
use App\Support\AuthErrorCodes;
use App\Support\LocaleNormalizer;
use App\Support\ThemeNormalizer;

final class SessionContextBuilder
{
    public function __construct(
        private readonly CommercialProfileResolver $commercialProfileResolver,
        private readonly InactivityTimeoutResolver $inactivityTimeoutResolver,
        private readonly UserRoleUnionService $userRoleUnionService,
    ) {}

    public function build(User $user, ?string $token = null): array
    {
        $union = $this->userRoleUnionService->resolveForUser($user);

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

        $context = [
            'user' => [
                'id' => $user->id,
                'displayName' => (string) ($user->name_user ?? $user->codigo),
                'login' => (string) $user->codigo,
            ],
            'functionalProfile' => $functionalProfile,
            'codCliente' => $codCliente,
            'codVendedor' => $codVendedor,
            'locale' => LocaleNormalizer::normalize($user->locale),
            'theme' => ThemeNormalizer::normalize($user->theme),
            'firstLogin' => (bool) $user->first_login,
            'inactivityTimeoutMinutes' => $this->inactivityTimeoutResolver->resolveMinutes(),
            'security' => [
                'roles' => $union->getRoleNames(),
                'accesoTotal' => $union->hasAccesoTotal(),
            ],
        ];

        if ($token !== null) {
            $context['token'] = $token;
        }

        return $context;
    }
}
