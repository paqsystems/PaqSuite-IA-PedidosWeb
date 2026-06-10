<?php

namespace App\Contracts\PedidosWeb;

use App\Models\PqPedidoswebArticulo;
use App\Models\PqPedidoswebDescuentoCantidad;
use App\Models\PqPedidoswebListaPreciosArticulo;
use App\Models\PqPedidoswebStock;

interface ArticuloRepositoryInterface
{
    public function findByCodigo(string $codigo): ?PqPedidoswebArticulo;

    public function findPrecioLista(int $codLista, string $codArticulo): ?PqPedidoswebListaPreciosArticulo;

    public function findStock(string $codArticulo): ?PqPedidoswebStock;

    public function findDescuentoCantidad(string $codArticulo, float $cantidad): ?PqPedidoswebDescuentoCantidad;
}
