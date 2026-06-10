<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Models\PqPedidoswebArticulo;
use App\Models\PqPedidoswebDescuentoCantidad;
use App\Models\PqPedidoswebListaPreciosArticulo;
use App\Models\PqPedidoswebStock;

final class ArticuloRepository implements ArticuloRepositoryInterface
{
    public function findByCodigo(string $codigo): ?PqPedidoswebArticulo
    {
        return PqPedidoswebArticulo::query()
            ->where('codigo', $codigo)
            ->first();
    }

    public function findPrecioLista(int $codLista, string $codArticulo): ?PqPedidoswebListaPreciosArticulo
    {
        return PqPedidoswebListaPreciosArticulo::query()
            ->where('cod_lista', $codLista)
            ->where('cod_articulo', $codArticulo)
            ->first();
    }

    public function findStock(string $codArticulo): ?PqPedidoswebStock
    {
        return PqPedidoswebStock::query()
            ->where('cod_articulo', $codArticulo)
            ->first();
    }

    public function findDescuentoCantidad(string $codArticulo, float $cantidad): ?PqPedidoswebDescuentoCantidad
    {
        return PqPedidoswebDescuentoCantidad::query()
            ->where('cod_articu', $codArticulo)
            ->where('cantidad', '<=', $cantidad)
            ->orderByDesc('cantidad')
            ->first();
    }
}
