<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Models\PqPedidoswebPedidoDetalle;
use Illuminate\Support\Collection;

final class PedidoDetalleRepository implements PedidoDetalleRepositoryInterface
{
    public function findByCodPedido(string $codPedido): Collection
    {
        return PqPedidoswebPedidoDetalle::query()
            ->where('cod_pedido', $codPedido)
            ->orderBy('renglon')
            ->get();
    }

    public function syncDetalle(string $codPedido, array $renglones): void
    {
        $this->deleteByCodPedido($codPedido);

        foreach ($renglones as $renglon) {
            PqPedidoswebPedidoDetalle::query()->create([
                ...$renglon,
                'cod_pedido' => $codPedido,
            ]);
        }
    }

    public function deleteByCodPedido(string $codPedido): int
    {
        return PqPedidoswebPedidoDetalle::query()
            ->where('cod_pedido', $codPedido)
            ->delete();
    }
}
