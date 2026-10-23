<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

interface IntegracionTransaccionHandlerInterface
{
    public function supportsSnapshot(): bool;

    /**
     * @param  array<string, mixed>  $item
     */
    public function keyForItem(array $item): ?string;

    /**
     * @param  array<string, mixed>  $item
     */
    public function processItem(
        array $item,
        IntegracionTransaccionLoteContext $context,
    ): IntegracionTransaccionItemResult;

    /**
     * @param  array<int, string|null>  $processedKeys
     * @return array<int, IntegracionTransaccionItemResult>
     */
    public function finalizeSnapshot(
        IntegracionTransaccionLoteContext $context,
        array $processedKeys,
    ): array;
}
