<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

final class IntegracionTransaccionLoteContext
{
    public function __construct(
        public readonly string $batchId,
        public readonly string $tenant,
        public readonly string $recurso,
        public readonly string $modo,
        public readonly string $fechaProceso,
        public readonly ?string $origen,
    ) {}
}
