<?php

namespace App\Repositories\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPresupuestoCierre;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;

final class PedidoRepository implements PedidoRepositoryInterface
{
    public function __construct(
        private readonly PedidosWebSchemaBootstrap $schemaBootstrap,
    ) {}

    private function cabeceraTable(): string
    {
        return 'pq_pedidosweb_pedidoscabecera';
    }
    public function findByCodPedido(string $codPedido, bool $withLock = false): ?PqPedidoswebPedidoCabecera
    {
        $query = PqPedidoswebPedidoCabecera::query()->where('cod_pedido', $codPedido);

        if ($withLock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function findWithDetalle(string $codPedido): ?PqPedidoswebPedidoCabecera
    {
        return PqPedidoswebPedidoCabecera::query()
            ->with(['detalles' => static fn ($query) => $query->orderBy('renglon')])
            ->where('cod_pedido', $codPedido)
            ->first();
    }

    public function insertCabecera(array $attributes): PqPedidoswebPedidoCabecera
    {
        $attributes = $this->schemaBootstrap->filterAttributes($this->cabeceraTable(), $attributes);

        return PqPedidoswebPedidoCabecera::query()->create($attributes);
    }

    public function updateCabecera(string $codPedido, array $attributes): bool
    {
        $attributes = $this->schemaBootstrap->filterAttributes($this->cabeceraTable(), $attributes);

        return (bool) PqPedidoswebPedidoCabecera::query()
            ->where('cod_pedido', $codPedido)
            ->update($attributes);
    }

    public function updateEstado(string $codPedido, int $estado): bool
    {
        return $this->updateCabecera($codPedido, ['estado' => $estado]);
    }

    public function deleteFisicoCabecera(string $codPedido): int
    {
        return PqPedidoswebPedidoCabecera::query()
            ->where('cod_pedido', $codPedido)
            ->delete();
    }

    public function insertPresupuestoCierre(array $attributes): PqPedidoswebPresupuestoCierre
    {
        return PqPedidoswebPresupuestoCierre::query()->create($attributes);
    }
}
