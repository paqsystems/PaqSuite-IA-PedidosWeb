<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroHandlerInterface;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroItemResult;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLoteContext;

final class IntegracionMaestroStubHandler implements IntegracionMaestroHandlerInterface
{
    public function supportsSnapshot(): bool
    {
        return true;
    }

    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult
    {
        $codProvin = $item['codProvin'] ?? $item['cod_provin'] ?? null;

        if (! is_string($codProvin) || trim($codProvin) === '') {
            return IntegracionMaestroItemResult::rejected(
                'integracion.maestro.itemInvalido',
                is_string($codProvin) ? $codProvin : null,
            );
        }

        if (($item['rechazar'] ?? false) === true) {
            return IntegracionMaestroItemResult::rejected('integracion.maestro.itemInvalido', $codProvin);
        }

        return IntegracionMaestroItemResult::applied($codProvin);
    }

    public function finalizeSnapshot(IntegracionMaestroLoteContext $context, array $processedKeys): array
    {
        return [];
    }
}
