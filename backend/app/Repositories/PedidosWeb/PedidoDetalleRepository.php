<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Models\PqPedidoswebPedidoDetalle;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use Illuminate\Support\Collection;

final class PedidoDetalleRepository implements PedidoDetalleRepositoryInterface
{
    public function __construct(
        private readonly PedidosWebSchemaBootstrap $schemaBootstrap,
    ) {}

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
            $attributes = $this->schemaBootstrap->mapDetalleAttributes([
                ...$renglon,
                'cod_pedido' => $codPedido,
            ]);

            PqPedidoswebPedidoDetalle::query()->create($attributes);
        }
    }

    public function deleteByCodPedido(string $codPedido): int
    {
        return PqPedidoswebPedidoDetalle::query()
            ->where('cod_pedido', $codPedido)
            ->delete();
    }
}
