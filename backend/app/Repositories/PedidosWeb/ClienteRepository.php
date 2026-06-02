<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\ClienteRepositoryInterface;
use App\Models\PqPedidoswebCliente;

final class ClienteRepository implements ClienteRepositoryInterface
{
    public function findByCodClient(string $codClient): ?PqPedidoswebCliente
    {
        return PqPedidoswebCliente::query()
            ->where('cod_client', $codClient)
            ->first();
    }

    public function findConDirecciones(string $codClient): ?PqPedidoswebCliente
    {
        return PqPedidoswebCliente::query()
            ->with(['direccionesEntrega' => static fn ($query) => $query->orderBy('id_de')])
            ->where('cod_client', $codClient)
            ->first();
    }
}
