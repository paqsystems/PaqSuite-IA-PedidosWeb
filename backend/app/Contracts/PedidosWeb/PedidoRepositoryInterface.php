<?php

namespace App\Contracts\PedidosWeb;

use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPresupuestoCierre;

interface PedidoRepositoryInterface
{
    public function findByCodPedido(string $codPedido, bool $withLock = false): ?PqPedidoswebPedidoCabecera;

    public function findWithDetalle(string $codPedido): ?PqPedidoswebPedidoCabecera;

    /**
     * @param array<string, mixed> $attributes
     */
    public function insertCabecera(array $attributes): PqPedidoswebPedidoCabecera;

    /**
     * @param array<string, mixed> $attributes
     */
    public function updateCabecera(string $codPedido, array $attributes): bool;

    public function updateEstado(string $codPedido, int $estado): bool;

    /** Eliminación física cabecera (detalle debe eliminarse aparte o vía service). */
    public function deleteFisicoCabecera(string $codPedido): int;

    /**
     * @param array<string, mixed> $attributes
     */
    public function insertPresupuestoCierre(array $attributes): PqPedidoswebPresupuestoCierre;
}
