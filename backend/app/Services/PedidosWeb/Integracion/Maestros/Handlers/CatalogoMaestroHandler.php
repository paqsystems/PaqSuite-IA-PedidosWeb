<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroHandlerInterface;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroItemResult;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLoteContext;
use App\Services\PedidosWeb\Integracion\Maestros\Repositories\MaestroSpRepository;
use Illuminate\Database\QueryException;

final class CatalogoMaestroHandler implements IntegracionMaestroHandlerInterface
{
    /**
     * @param  array<string, mixed>  $definition
     */
    public function __construct(
        private readonly MaestroSpRepository $repository,
        private readonly array $definition = [],
    ) {}

    public function supportsSnapshot(): bool
    {
        return (bool) ($this->definition['supports_snapshot'] ?? false);
    }

    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult
    {
        $item = $this->normalize($item);
        $claveNatural = $this->naturalKey($item);

        if ($claveNatural === null) {
            return IntegracionMaestroItemResult::rejected('integracion.maestro.itemInvalido');
        }

        $procedure = (string) ($this->definition['apply'] ?? '');

        try {
            $result = $this->repository->apply($procedure, $context, $item);
        } catch (QueryException $exception) {
            if (str_contains(strtoupper($exception->getMessage()), '[ITEM_REJECTED]')) {
                return IntegracionMaestroItemResult::rejected(
                    'integracion.maestro.fkFaltante',
                    $claveNatural,
                );
            }

            throw $exception;
        }

        $clave = $result['clave'] ?? $claveNatural;
        $status = $result['status'];

        if ($status === 'omitted') {
            return IntegracionMaestroItemResult::omitted($clave);
        }

        if ($status === 'rejected') {
            return IntegracionMaestroItemResult::rejected(
                $result['error_code'] ?: 'integracion.maestro.itemInvalido',
                $clave,
            );
        }

        return IntegracionMaestroItemResult::applied($clave);
    }

    public function finalizeSnapshot(IntegracionMaestroLoteContext $context, array $processedKeys): array
    {
        $procedure = (string) ($this->definition['finalize'] ?? '');

        if ($procedure === '') {
            return [];
        }

        return array_map(
            static function (array $result): IntegracionMaestroItemResult {
                if ($result['status'] === 'rejected') {
                    return IntegracionMaestroItemResult::rejected(
                        $result['error_code'] ?: 'integracion.maestro.deleteReferenciado',
                        $result['clave'],
                    );
                }

                return IntegracionMaestroItemResult::omitted($result['clave']);
            },
            $this->repository->finalize($procedure, $context),
        );
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalize(array $item): array
    {
        if ($contextResource = ($this->definition['resource'] ?? null)) {
            if ($contextResource === 'vendedores') {
                unset($item['codLogin'], $item['cod_login']);
            }
        }

        foreach (['supervisor', 'incluyeIva', 'stockeable', 'especial'] as $field) {
            if (array_key_exists($field, $item)) {
                $item[$field] = filter_var($item[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function naturalKey(array $item): ?string
    {
        $fields = $this->definition['key'] ?? [];
        $values = [];

        foreach ((array) $fields as $field) {
            $value = $item[$field] ?? null;
            $values[] = $value === null ? null : (string) $value;
        }

        return $values === [] || in_array(null, $values, true) ? null : implode('|', $values);
    }
}
