<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

use PaqSuite\LaravelCore\Importation\Contracts\ImportationDomainAdapter;

final class ImportationMaestroDomainAdapter implements ImportationDomainAdapter
{
    public function __construct(private readonly IntegracionMaestroRegistry $registry)
    {
    }

    public function process(
        string $resource,
        string $importacionId,
        int|string|null $tenantId,
        array $batches,
    ): array {
        $handler = $this->registry->resolve($resource);
        if ($handler === null) {
            throw new \InvalidArgumentException('integracion.maestro.recursoDesconocido');
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
            IntegracionMaestroItemResult $result,
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
            $processedKeys[] = $result->clave;
            if ($result->isDeleted()) {
                $deleted++;
                return;
            }
            if ($result->isRejected()) {
                $this->incrementErrorCounter($result->operation, $addedErrors, $updatedErrors, $deletedErrors);
                $errors[] = [
                    'indice' => $index,
                    'clave' => $result->clave,
                    'codigo' => $result->errorCode ?? 'integracion.maestro.itemInvalido',
                ];
                return;
            }

            if ($result->operation === 'added') {
                $added++;
            } else {
                $updated++;
            }
        };

        foreach ($batches as $batch) {
            $mode = (string) ($batch['mode'] ?? $mode);
            $lastProcessDate = (string) ($batch['processDate'] ?? $lastProcessDate);
            $payload = is_array($batch['payload'] ?? null) ? $batch['payload'] : [];
            $origin = isset($payload['origen']) ? (string) $payload['origen'] : $origin;
            $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
            $context = new IntegracionMaestroLoteContext(
                $domainBatchId,
                (string) $tenantId,
                $resource,
                $mode,
                $lastProcessDate,
                $origin,
            );

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    $updatedErrors++;
                    $processed++;
                    $errors[] = [
                        'indice' => (int) $index,
                        'clave' => null,
                        'codigo' => 'integracion.maestro.itemInvalido',
                    ];
                    continue;
                }

                $consumeResult($handler->processItem($item, $context), (int) $index);

                if ($resource === 'clientes') {
                    foreach (['direcciones' => 'clientes-direcciones', 'contactos' => 'clientes-contactos'] as $childrenKey => $childResource) {
                        $childHandler = $this->registry->resolve($childResource);
                        $children = is_array($item[$childrenKey] ?? null) ? $item[$childrenKey] : [];
                        if ($childHandler === null || $children === []) {
                            continue;
                        }
                        foreach ($children as $childIndex => $child) {
                            if (! is_array($child)) {
                                $updatedErrors++;
                                $processed++;
                                $errors[] = [
                                    'indice' => (int) $index.'.'.(int) $childIndex,
                                    'clave' => null,
                                    'codigo' => 'integracion.maestro.itemInvalido',
                                ];
                                continue;
                            }
                            $child['codClient'] ??= $item['codClient'] ?? $item['cod_client'] ?? null;
                            $consumeResult(
                                $childHandler->processItem($child, $context),
                                (int) $index,
                            );
                        }
                    }
                }
            }
        }

        if ($mode === 'snapshot' && $handler->supportsSnapshot()) {
            $context = new IntegracionMaestroLoteContext(
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

                $processed++;
                if ($result->isDeleted()) {
                    $deleted++;
                    continue;
                }

                $deletedErrors++;
                $errors[] = [
                    'indice' => $processed,
                    'clave' => $result->clave,
                    'codigo' => $result->errorCode ?? 'integracion.maestro.deleteReferenciado',
                ];
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

    private function incrementErrorCounter(
        string $operation,
        int &$addedErrors,
        int &$updatedErrors,
        int &$deletedErrors,
    ): void {
        if ($operation === 'added') {
            $addedErrors++;
        } elseif ($operation === 'deleted') {
            $deletedErrors++;
        } else {
            $updatedErrors++;
        }
    }
}
