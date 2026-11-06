<?php

namespace App\Services\PedidosWeb\Integracion\Maestros\Handlers;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroText;

final class ClientesMaestroHandler extends IntegracionMaestroJsonHandler
{
    protected function normalize(array $item): array
    {
        unset($item['codLogin'], $item['cod_login']);

        foreach (['leyenda1', 'leyenda2', 'leyenda3', 'leyenda4', 'leyenda5'] as $field) {
            if (array_key_exists($field, $item) && $item[$field] !== null) {
                $item[$field] = IntegracionMaestroText::truncateUnicode60((string) $item[$field]);
            }
        }

        if (array_key_exists('eliminarSiAusente', $item)) {
            $item['eliminarSiAusente'] = filter_var(
                $item['eliminarSiAusente'],
                FILTER_VALIDATE_BOOLEAN,
            );
        }

        return $item;
    }
}
