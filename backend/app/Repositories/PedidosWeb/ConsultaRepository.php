<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\ConsultaRepositoryInterface;
use App\Models\PqPedidoswebCheque;
use App\Models\PqPedidoswebDeuda;
use Illuminate\Support\Collection;

final class ConsultaRepository implements ConsultaRepositoryInterface
{
    public function findDeudaByCodCliente(string $codCliente): Collection
    {
        return PqPedidoswebDeuda::query()
            ->where('cod_cliente', $codCliente)
            ->orderByDesc('fecha_vto')
            ->get();
    }

    public function findChequesByCodClient(string $codClient): Collection
    {
        return PqPedidoswebCheque::query()
            ->where('cod_client', $codClient)
            ->orderByDesc('fecha')
            ->get();
    }
}
