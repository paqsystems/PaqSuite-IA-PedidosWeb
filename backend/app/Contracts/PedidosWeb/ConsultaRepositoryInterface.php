<?php

namespace App\Contracts\PedidosWeb;

use Illuminate\Support\Collection;

interface ConsultaRepositoryInterface
{
    /** @return Collection<int, object> */
    public function findDeudaByCodCliente(string $codCliente): Collection;

    /** @return Collection<int, object> */
    public function findChequesByCodClient(string $codClient): Collection;
}
