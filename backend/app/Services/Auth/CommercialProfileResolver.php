<?php

namespace App\Services\Auth;

use App\Models\PqPedidoswebCliente;
use App\Models\PqPedidoswebLogin;
use App\Models\PqPedidoswebVendedor;
use App\Models\User;

final class CommercialProfileResolver
{
    /**
     * @return array{cliente: ?PqPedidoswebCliente, vendedor: ?PqPedidoswebVendedor}
     */
    public function resolveForUser(User $user): array
    {
        $login = PqPedidoswebLogin::query()
            ->where('usuario', $user->codigo)
            ->first();

        $loginCode = $login?->cod_usuario_web ?? $user->codigo;

        $cliente = PqPedidoswebCliente::query()
            ->where('cod_login', $loginCode)
            ->first();

        $vendedor = PqPedidoswebVendedor::query()
            ->where('cod_login', $loginCode)
            ->first();

        return [
            'cliente' => $cliente,
            'vendedor' => $vendedor,
        ];
    }
}
