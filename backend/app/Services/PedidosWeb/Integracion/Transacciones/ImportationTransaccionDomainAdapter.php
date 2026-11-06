<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

use PaqSuite\LaravelCore\Importation\Contracts\ImportationDomainAdapter;

final class ImportationTransaccionDomainAdapter implements ImportationDomainAdapter
{
    public function __construct(
        private readonly IntegracionTransaccionRegistry $registry,
    ) {}

    public function process(
        string $resource,
        string $importacionId,
        int|string|null $tenantId,
        array $batches,
    ): array {
        $handler = $this->registry->resolve($resource);
        if ($handler === null) {
            throw new \InvalidArgumentException('integracion.transaccion.recursoDesconocido');
        }

        $startedAt = microtime(true);
        $processed = 0;
        $added = 0;
        $updated = 0;
        $deleted = 0;
        $addedErrors = 0;
        $updatedErrors = 0;
        $deletedErrors = 0;
        $errors = [];
        $processedKeys = [];
        $mode = 'delta';
        $lastProcessDate = '';
        $origin = null;
        $domainBatchId = $this->domainBatchId($resource, $importacionId, $tenantId);

        $consumeResult = function (
            IntegracionTransaccionItemResult $result,
            int $index,
        ) use (
            &$processed,
            &$added,
            &$updated,
            &$deleted,
            &$addedErrors,
            &$updatedErrors,
            &$deletedErrors,
            &$errors,
            &$processedKeys,
        ): void {
            if ($result->isOmitted()) {
                return;
            }

            $processed++;
            if ($result->clave !== null) {
                $processedKeys[] = $result->clave;
            }

            if ($result->isRejected()) {
                $updatedErrors++;
                $errors[] = [
                    'indice' => $index,
                    'clave' => $result->clave,
                    'codigo' => $result->errorCode ?? 'integracion.transaccion.itemInvalido',
                ];

                return;
            }

            $updated++;
        };

        foreach ($batches as $batch) {
            $mode = (string) ($batch['mode'] ?? $mode);
            $lastProcessDate = (string) ($batch['processDate'] ?? $lastProcessDate);
            $payload = is_array($batch['payload'] ?? null) ? $batch['payload'] : [];
            $origin = isset($payload['origen']) ? (string) $payload['origen'] : $origin;
            $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
            $context = new IntegracionTransaccionLoteContext(
                $domainBatchId,
                (string) $tenantId,
                $resource,
                $mode,
                $lastProcessDate,
                $origin,
            );
            $seenKeys = [];

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    $processed++;
                    $updatedErrors++;
                    $errors[] = [
                        'indice' => (int) $index,
                        'clave' => null,
                        'codigo' => 'integracion.transaccion.itemInvalido',
                    ];
                    continue;
                }

                $itemKey = $handler->keyForItem($item);
                if ($itemKey !== null && isset($seenKeys[$itemKey])) {
                    $consumeResult(
                        IntegracionTransaccionItemResult::rejected(
                            $itemKey,
                            'integracion.transaccion.duplicado',
                        ),
                        (int) $index,
                    );
                    continue;
                }

                if ($itemKey !== null) {
                    $seenKeys[$itemKey] = true;
                }

                $consumeResult($handler->processItem($item, $context), (int) $index);
            }
        }

        if ($mode === 'snapshot' && $handler->supportsSnapshot()) {
            $context = new IntegracionTransaccionLoteContext(
                $domainBatchId,
                (string) $tenantId,
                $resource,
                $mode,
                $lastProcessDate,
                $origin,
            );

            foreach ($handler->finalizeSnapshot($context, $processedKeys) as $result) {
                if ($result->isOmitted()) {
                    continue;
                }

                if ($result->isRejected()) {
                    $processed++;
                    $updatedErrors++;
                    $errors[] = [
                        'indice' => $processed,
                        'clave' => $result->clave,
                        'codigo' => $result->errorCode ?? 'integracion.transaccion.snapshotAbortado',
                    ];
                }
            }
        }

        return [
            'status' => 'completed',
            'durationSeconds' => round(microtime(true) - $startedAt, 3),
            'processed' => $processed,
            'added' => $added,
            'updated' => $updated,
            'deleted' => $deleted,
            'addedErrors' => $addedErrors,
            'updatedErrors' => $updatedErrors,
            'deletedErrors' => $deletedErrors,
            'errors' => $errors,
        ];
    }

    private function domainBatchId(
        string $resource,
        string $importacionId,
        int|string|null $tenantId,
    ): string {
        $hex = substr(hash('sha256', $resource.'|'.(string) $tenantId.'|'.$importacionId), 0, 32);

        return substr($hex, 0, 8).'-'
            .substr($hex, 8, 4).'-'
            .substr($hex, 12, 4).'-'
            .substr($hex, 16, 4).'-'
            .substr($hex, 20, 12);
    }
}
