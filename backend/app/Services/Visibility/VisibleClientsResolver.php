<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\PqPedidoswebCliente;
use App\Models\User;
use App\Services\Auth\CommercialProfileResolver;
use App\Support\AuthErrorCodes;
use Illuminate\Database\Eloquent\Builder;

final class VisibleClientsResolver
{
    public function __construct(
        private readonly CommercialProfileResolver $commercialProfileResolver,
    ) {}

    public function visibleClientsForUser(User $user): Builder
    {
        $commercialProfile = $this->commercialProfileResolver->resolveForUser($user);
        $cliente = $commercialProfile['cliente'];
        $vendedor = $commercialProfile['vendedor'];

        if ($cliente !== null && $vendedor !== null) {
            throw new AuthFlowException(
                AuthErrorCodes::noCommercialProfile,
                'auth.noCommercialProfile',
                403
            );
        }

        $query = PqPedidoswebCliente::query();

        if ($cliente !== null) {
            return $query->where('cod_client', (string) $cliente->cod_client);
        }

        if ($vendedor === null) {
            throw new AuthFlowException(
                AuthErrorCodes::noCommercialProfile,
                'auth.noCommercialProfile',
                403
            );
        }

        if ((bool) $vendedor->supervisor) {
            return $query;
        }

        return $query->where('cod_vended', (string) $vendedor->cod_vended);
    }
}
