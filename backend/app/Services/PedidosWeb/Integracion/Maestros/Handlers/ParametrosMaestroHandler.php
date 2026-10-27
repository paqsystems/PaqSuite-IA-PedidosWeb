<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroItemResult;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLoteContext;

final class ParametrosMaestroHandler extends IntegracionMaestroJsonHandler
{
    private const valorFieldByTipo = [
        'B' => 'valorBool',
        'I' => 'valorInt',
        'N' => 'valorDecimal',
        'D' => 'valorDateTime',
        'T' => 'valorText',
        'S' => 'valorString',
    ];

    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult
    {
        $clave = isset($item['clave']) ? trim((string) $item['clave']) : '';
        $whitelist = (array) config('integracion_maestros_parametros_whitelist', []);
        $tipoWhitelist = $whitelist[$clave] ?? null;

        if ($clave === '' || $tipoWhitelist === null) {
            return IntegracionMaestroItemResult::rejected(
                'integracion.maestro.parametroNoPermitido',
                $clave !== '' ? $clave : null,
            );
        }

        if ($this->countValorFieldsPresent($item) > 1) {
            return IntegracionMaestroItemResult::rejected(
                'integracion.maestro.itemInvalido',
                $clave,
            );
        }

        $tipo = $this->resolveTipoValor($item, $tipoWhitelist);
        if ($tipo === null) {
            return IntegracionMaestroItemResult::rejected(
                'integracion.maestro.itemInvalido',
                $clave,
            );
        }

        $field = self::valorFieldByTipo[$tipo] ?? 'valorString';
        if (! array_key_exists($field, $item)) {
            return IntegracionMaestroItemResult::rejected(
                'integracion.maestro.itemInvalido',
                $clave,
            );
        }

        $item['programa'] = 'PedidosWeb';
        $item['tipoValor'] = $tipo;

        return parent::processItem($item, $context);
    }

    protected function naturalKey(array $item): ?string
    {
        $clave = trim((string) ($item['clave'] ?? ''));

        return $clave === '' ? null : 'PedidosWeb|'.$clave;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveTipoValor(array $item, string $tipoWhitelist): ?string
    {
        $sent = isset($item['tipoValor']) ? strtoupper(trim((string) $item['tipoValor'])) : '';
        if ($sent !== '') {
            return $sent === $tipoWhitelist ? $sent : null;
        }

        $inferred = $this->inferTipoFromValorFields($item);
        if ($inferred !== null) {
            return $inferred === $tipoWhitelist ? $inferred : null;
        }

        return $tipoWhitelist;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function inferTipoFromValorFields(array $item): ?string
    {
        $found = [];
        foreach (self::valorFieldByTipo as $tipo => $field) {
            if (! array_key_exists($field, $item) || $item[$field] === null) {
                continue;
            }
            $found[] = $tipo;
        }

        return count($found) === 1 ? $found[0] : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function countValorFieldsPresent(array $item): int
    {
        $count = 0;
        foreach (self::valorFieldByTipo as $field) {
            if (array_key_exists($field, $item) && $item[$field] !== null) {
                $count++;
            }
        }

        return $count;
    }
}
