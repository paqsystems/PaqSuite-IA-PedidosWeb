<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroItemResult;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroLoteContext;

final class ParametrosMaestroHandler extends IntegracionMaestroJsonHandler
{
    public function processItem(array $item, IntegracionMaestroLoteContext $context): IntegracionMaestroItemResult
    {
        $clave = isset($item['clave']) ? trim((string) $item['clave']) : '';
        $whitelist = (array) config('integracion_maestros_parametros_whitelist', []);
        $tipo = $whitelist[$clave] ?? null;

        if ($clave === '' || $tipo === null) {
            return IntegracionMaestroItemResult::rejected(
                'integracion.maestro.parametroNoPermitido',
                $clave !== '' ? $clave : null,
            );
        }

        $field = match ($tipo) {
            'B' => 'valorBool',
            'I' => 'valorInt',
            'N' => 'valorDecimal',
            'D' => 'valorDateTime',
            'T' => 'valorText',
            default => 'valorString',
        };

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
}
