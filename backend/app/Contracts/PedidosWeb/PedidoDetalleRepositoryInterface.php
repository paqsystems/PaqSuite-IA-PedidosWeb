<?php

namespace App\Contracts\PedidosWeb;

use App\Models\PqPedidoswebPedidoDetalle;
use Illuminate\Support\Collection;

interface PedidoDetalleRepositoryInterface
{
    /** @return Collection<int, PqPedidoswebPedidoDetalle> */
    public function findByCodPedido(string $codPedido): Collection;

    /**
     * Reemplaza todos los renglones del comprobante (delete + insert).
     *
     * @param list<array<string, mixed>> $renglones
     */
    public function syncDetalle(string $codPedido, array $renglones): void;

    public function deleteByCodPedido(string $codPedido): int;
}
