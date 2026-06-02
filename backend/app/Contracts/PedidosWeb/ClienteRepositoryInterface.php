<?php

namespace App\Contracts\PedidosWeb;

use App\Models\PqPedidoswebCliente;

interface ClienteRepositoryInterface
{
    public function findByCodClient(string $codClient): ?PqPedidoswebCliente;

    public function findConDirecciones(string $codClient): ?PqPedidoswebCliente;
}
