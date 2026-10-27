<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

interface IntegracionMaestroHandlerInterface
{
    public function supportsSnapshot(): bool;

    /**
     * @param  array<string, mixed>  $item
     */
    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult;

    /**
     * Ejecuta las operaciones de cierre de un snapshot después del loop.
     *
     * @param  array<int, string|null>  $processedKeys
     */
    /**
     * @return array<int, IntegracionMaestroItemResult>
     */
    public function finalizeSnapshot(
        IntegracionMaestroLoteContext $context,
        array $processedKeys,
    ): array;
}
