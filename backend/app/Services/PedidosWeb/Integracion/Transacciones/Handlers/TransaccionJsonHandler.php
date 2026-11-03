<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones\Handlers;

use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionHandlerInterface;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionItemNormalizer;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionItemResult;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionLoteContext;
use App\Services\PedidosWeb\Integracion\Transacciones\Repositories\TransaccionSpRepository;
use Illuminate\Database\QueryException;

final class TransaccionJsonHandler implements IntegracionTransaccionHandlerInterface
{
    /**
     * @param  array<string, mixed>  $definition
     */
    public function __construct(
        private readonly TransaccionSpRepository $repository,
        private readonly array $definition = [],
    ) {}

    public function supportsSnapshot(): bool
    {
        return (bool) ($this->definition['supports_snapshot'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function keyForItem(array $item): ?string
    {
        return $this->key($item);
    }

    public function processItem(
        array $item,
        IntegracionTransaccionLoteContext $context,
    ): IntegracionTransaccionItemResult {
        $item = IntegracionTransaccionItemNormalizer::normalize($context->recurso, $item);
        $item = $this->normalizeEliminarSiAusente($item);
        $clave = $this->key($item);

        $deleteExplicit = $this->isExplicitDeleteItem($item);
        if ($deleteExplicit && $context->modo !== 'delta') {
            return IntegracionTransaccionItemResult::rejected(
                $clave,
                'integracion.transaccion.eliminarSoloDelta',
            );
        }

        $requiredFields = (array) ($this->definition['required'] ?? []);
        if ($deleteExplicit) {
            $requiredFields = (array) ($this->definition['key'] ?? []);
        }

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $item) || $item[$field] === null || $item[$field] === '') {
                return IntegracionTransaccionItemResult::rejected(
                    $clave,
                    'integracion.transaccion.itemInvalido',
                );
            }
        }

        if ($context->recurso === 'stock') {
            foreach (['comprometidoWeb', 'stockBase', 'comprometidoBase', 'disponibleNeto', 'disponibleNetoBase'] as $field) {
                if (array_key_exists($field, $item)) {
                    return IntegracionTransaccionItemResult::rejected(
                        $clave,
                        'integracion.transaccion.campoNoPermitido',
                    );
                }
            }
        }

        try {
            $result = $this->repository->apply(
                (string) ($this->definition['apply'] ?? ''),
                $context,
                $item,
            );
        } catch (QueryException $exception) {
            if (str_contains(strtoupper($exception->getMessage()), '[ITEM_REJECTED]')) {
                return IntegracionTransaccionItemResult::rejected(
                    $clave,
                    'integracion.transaccion.itemInvalido',
                );
            }

            throw $exception;
        }

        $status = $result['status'];
        $resultKey = $result['clave'] ?? $clave;

        return match ($status) {
            'omitted' => IntegracionTransaccionItemResult::omitted($resultKey, $result['error_code']),
            'rejected' => IntegracionTransaccionItemResult::rejected(
                $resultKey,
                $result['error_code'] ?? 'integracion.transaccion.itemInvalido',
            ),
            default => IntegracionTransaccionItemResult::applied($resultKey),
        };
    }

    public function finalizeSnapshot(
        IntegracionTransaccionLoteContext $context,
        array $processedKeys,
    ): array {
        if (! $this->supportsSnapshot()) {
            return [];
        }

        try {
            $rows = $this->repository->finalize(
                (string) ($this->definition['finalize'] ?? ''),
                $context,
            );
        } catch (QueryException) {
            return [
                IntegracionTransaccionItemResult::rejected(
                    null,
                    'integracion.transaccion.snapshotAbortado',
                ),
            ];
        }

        return array_map(
            static fn (array $row): IntegracionTransaccionItemResult => match ($row['status']) {
                'rejected' => IntegracionTransaccionItemResult::rejected(
                    $row['clave'],
                    $row['error_code'] ?? 'integracion.transaccion.snapshotAbortado',
                ),
                default => IntegracionTransaccionItemResult::omitted(
                    $row['clave'],
                    $row['error_code'],
                ),
            },
            $rows,
        );
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function key(array $item): ?string
    {
        $parts = [];

        foreach ((array) ($this->definition['key'] ?? []) as $field) {
            if (! array_key_exists($field, $item) || $item[$field] === null || $item[$field] === '') {
                return null;
            }
            $parts[] = (string) $item[$field];
        }

        return $parts === [] ? null : implode('|', $parts);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeEliminarSiAusente(array $item): array
    {
        if (! array_key_exists('eliminarSiAusente', $item)) {
            return $item;
        }

        $item['eliminarSiAusente'] = filter_var(
            $item['eliminarSiAusente'],
            FILTER_VALIDATE_BOOLEAN,
        );

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isExplicitDeleteItem(array $item): bool
    {
        return ($item['eliminarSiAusente'] ?? false) === true;
    }
}
