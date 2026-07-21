<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Models\PqPedidoswebArticulo;
use App\Models\PqPedidoswebDescuentoCantidad;
use App\Models\PqPedidoswebListaPreciosArticulo;
use App\Models\PqPedidoswebStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ArticuloRepository implements ArticuloRepositoryInterface
{
    public function findByCodigo(string $codigo): ?PqPedidoswebArticulo
    {
        return $this->findByCodigoColumn(PqPedidoswebArticulo::query(), 'codigo', $codigo);
    }

    public function findPrecioLista(int $codLista, string $codArticulo): ?PqPedidoswebListaPreciosArticulo
    {
        $codArticulo = trim($codArticulo);
        if ($codArticulo === '' || $codLista <= 0) {
            return null;
        }

        $exact = PqPedidoswebListaPreciosArticulo::query()
            ->where('cod_lista', $codLista)
            ->where('cod_articulo', $codArticulo)
            ->first();

        if ($exact !== null) {
            return $exact;
        }

        return PqPedidoswebListaPreciosArticulo::query()
            ->where('cod_lista', $codLista)
            ->whereRaw("REPLACE(cod_articulo, ' ', '') = REPLACE(?, ' ', '')", [$codArticulo])
            ->first();
    }

    public function findStock(string $codArticulo): ?PqPedidoswebStock
    {
        return $this->findByCodigoColumn(PqPedidoswebStock::query(), 'cod_articulo', $codArticulo);
    }

    public function findDescuentoCantidad(string $codArticulo, float $cantidad): ?PqPedidoswebDescuentoCantidad
    {
        $codArticulo = trim($codArticulo);
        if ($codArticulo === '') {
            return null;
        }

        $query = PqPedidoswebDescuentoCantidad::query()
            ->where('cantidad', '<=', $cantidad)
            ->orderByDesc('cantidad');

        $exact = (clone $query)->where('cod_articu', $codArticulo)->first();
        if ($exact !== null) {
            return $exact;
        }

        return $query
            ->whereRaw("REPLACE(cod_articu, ' ', '') = REPLACE(?, ' ', '')", [$codArticulo])
            ->first();
    }

    /**
     * @template TModel of Model
     * @param  Builder<TModel>  $query
     * @return TModel|null
     */
    private function findByCodigoColumn(Builder $query, string $column, string $codigo): ?Model
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return null;
        }

        $exact = (clone $query)->where($column, $codigo)->first();
        if ($exact !== null) {
            return $exact;
        }

        // ERP (Tango/Ankas): códigos rellenados con espacios internos ("AB         0100" vs "AB 0100").
        return $query
            ->whereRaw("REPLACE({$column}, ' ', '') = REPLACE(?, ' ', '')", [$codigo])
            ->first();
    }
}
