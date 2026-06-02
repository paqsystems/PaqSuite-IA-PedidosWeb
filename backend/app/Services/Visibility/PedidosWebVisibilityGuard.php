<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Support\VisibilityErrorCodes;

class PedidosWebVisibilityGuard
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
    ) {}

    public function ensureCodClienteVisible(User $user, string $codCliente): void
    {
        if ($codCliente === '') {
            $this->throwNotFound();
        }

        $isVisible = $this->visibleClientsResolver
            ->visibleClientsForUser($user)
            ->where('cod_client', $codCliente)
            ->exists();

        if (! $isVisible) {
            $this->throwNotFound();
        }
    }

    public function ensureComprobanteVisible(
        User $user,
        string $codPedido,
        bool $lockForUpdate = false
    ): PqPedidoswebPedidoCabecera {
        $query = PqPedidoswebPedidoCabecera::query()
            ->where('cod_pedido', $codPedido)
            ->whereIn(
                'cod_cliente',
                $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client')
            );

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $comprobante = $query->first();

        if ($comprobante === null) {
            $this->throwNotFound();
        }

        return $comprobante;
    }

    private function throwNotFound(): never
    {
        throw new AuthFlowException(
            VisibilityErrorCodes::resourceNotFound,
            'resource.notFound',
            404
        );
    }
}
