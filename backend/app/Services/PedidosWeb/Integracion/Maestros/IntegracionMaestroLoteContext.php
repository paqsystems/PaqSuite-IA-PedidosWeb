<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

final class IntegracionMaestroLoteContext
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
