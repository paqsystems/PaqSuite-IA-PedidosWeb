<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroHandlerInterface;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroItemResult;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLoteContext;

/** Stub TR-22a — reemplazado en TR-22b por handler + SP real. */
final class IntegracionMaestroNoSnapshotStubHandler implements IntegracionMaestroHandlerInterface
{
    public function supportsSnapshot(): bool
    {
        return false;
    }

    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult
    {
        return IntegracionMaestroItemResult::applied(null);
    }

    public function finalizeSnapshot(IntegracionMaestroLoteContext $context, array $processedKeys): array
    {
        return [];
    }
}
