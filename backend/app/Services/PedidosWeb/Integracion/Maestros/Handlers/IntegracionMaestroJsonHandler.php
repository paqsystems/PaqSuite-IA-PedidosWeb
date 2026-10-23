<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroHandlerInterface;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroItemResult;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLoteContext;
use App\Services\PedidosWeb\Integracion\Maestros\Repositories\MaestroSpRepository;
use Illuminate\Database\QueryException;

abstract class IntegracionMaestroJsonHandler implements IntegracionMaestroHandlerInterface
{
    public function __construct(
        protected readonly MaestroSpRepository $repository,
        protected readonly array $definition = [],
    ) {}

    public function supportsSnapshot(): bool
    {
        return (bool) ($this->definition['supports_snapshot'] ?? false);
    }

    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult
    {
        $item = $this->normalize($item);
        $clave = $this->naturalKey($item);

        if ($clave === null) {
            return IntegracionMaestroItemResult::rejected('integracion.maestro.itemInvalido');
        }

        try {
            $result = $this->repository->apply(
                (string) $this->definition['apply'],
                $context,
                $item,
            );
        } catch (QueryException $exception) {
            if (str_contains(strtoupper($exception->getMessage()), '[ITEM_REJECTED]')) {
                return IntegracionMaestroItemResult::rejected(
                    'integracion.maestro.fkFaltante',
                    $clave,
                );
            }

            throw $exception;
        }

        $status = $result['status'] ?? 'applied';
        $resultKey = $result['clave'] ?? $clave;

        if ($status === 'omitted') {
            return IntegracionMaestroItemResult::omitted($resultKey);
        }

        if ($status === 'rejected') {
            return IntegracionMaestroItemResult::rejected(
                $result['error_code'] ?: 'integracion.maestro.itemInvalido',
                $resultKey,
            );
        }

        return IntegracionMaestroItemResult::applied($resultKey);
    }

    public function finalizeSnapshot(IntegracionMaestroLoteContext $context, array $processedKeys): array
    {
        return [];
    }

    protected function normalize(array $item): array
    {
        return $item;
    }

    protected function naturalKey(array $item): ?string
    {
        $values = [];
        foreach ((array) ($this->definition['key'] ?? []) as $field) {
            $value = $item[$field] ?? null;
            $values[] = $value === null || $value === '' ? null : (string) $value;
        }

        return $values === [] || in_array(null, $values, true)
            ? null
            : implode('|', $values);
    }
}
