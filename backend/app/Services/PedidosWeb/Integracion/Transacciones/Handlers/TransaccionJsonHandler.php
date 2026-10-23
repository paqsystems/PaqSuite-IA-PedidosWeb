<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones\Handlers;

use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionHandlerInterface;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionItemResult;
use App\Services\PedidosWeb\Integracion\Transacciones\IntegracionTransaccionLoteContext;
use App\Services\PedidosWeb\Integracion\Transacciones\Repositories\TransaccionSpRepository;

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
        $clave = $this->key($item);

        foreach ((array) ($this->definition['required'] ?? []) as $field) {
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

        $result = $this->repository->apply(
            (string) ($this->definition['apply'] ?? ''),
            $context,
            $item,
        );

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
            $this->repository->finalize(
                (string) ($this->definition['finalize'] ?? ''),
                $context,
            ),
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
}
