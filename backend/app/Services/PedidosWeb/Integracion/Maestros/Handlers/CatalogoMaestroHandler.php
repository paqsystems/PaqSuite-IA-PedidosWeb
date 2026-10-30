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
                    'modified',
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
                (string) ($result['operation'] ?? 'modified'),
            );
        }

        if (
            $status === 'deleted'
            || ($status === 'applied' && ($result['operation'] ?? '') === 'deleted')
        ) {
            return IntegracionMaestroItemResult::deleted($clave);
        }

        return IntegracionMaestroItemResult::applied(
            $clave,
            (string) ($result['operation'] ?? 'modified'),
        );
    }

    public function finalizeSnapshot(IntegracionMaestroLoteContext $context, array $processedKeys): array
    {
        $procedure = (string) ($this->definition['finalize'] ?? '');

        if ($procedure === '') {
            return [];
        }

        return array_map(
            static function (array $result): IntegracionMaestroItemResult {
                if ($result['status'] === 'deleted') {
                    return IntegracionMaestroItemResult::deleted($result['clave']);
                }

                if (
                    $result['status'] === 'rejected'
                    || ($result['status'] === 'omitted' && ($result['error_code'] ?? '') !== '')
                ) {
                    return IntegracionMaestroItemResult::rejected(
                        $result['error_code'] ?: 'integracion.maestro.deleteReferenciado',
                        $result['clave'],
                        'deleted',
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
        $contextResource = $this->definition['resource'] ?? null;

        if ($contextResource === 'vendedores') {
            unset($item['codLogin'], $item['cod_login']);
        }

        if ($contextResource === 'listaprecios') {
            $item = $this->normalizeListapreciosItem($item);
        }

        if ($contextResource === 'escalas') {
            if (! array_key_exists('descripEs', $item) && array_key_exists('descripcion', $item)) {
                $item['descripEs'] = $item['descripcion'];
            }
        }

        foreach (['supervisor', 'incluyeIva', 'stockeable', 'especial', 'eliminarSiAusente'] as $field) {
            if (array_key_exists($field, $item)) {
                $item[$field] = filter_var($item[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeListapreciosItem(array $item): array
    {
        if (! array_key_exists('codLista', $item) && array_key_exists('codigo', $item)) {
            $item['codLista'] = $item['codigo'];
        }

        $priceLines = $item['articulos'] ?? null;
        if (! is_array($priceLines) && is_array($item['items'] ?? null)) {
            $priceLines = $item['items'];
        }

        if (! is_array($priceLines)) {
            return $item;
        }

        $item['articulos'] = array_values(array_map(
            static function (mixed $line): mixed {
                if (! is_array($line)) {
                    return $line;
                }

                if (! array_key_exists('codArticulo', $line) && array_key_exists('codigo', $line)) {
                    $line['codArticulo'] = $line['codigo'];
                }

                return $line;
            },
            $priceLines,
        ));

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
