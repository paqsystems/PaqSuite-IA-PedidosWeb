<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

final class IntegracionMaestroText
{
    public static function truncateUnicode60(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return function_exists('mb_substr')
            ? mb_substr($value, 0, 60, 'UTF-8')
            : substr($value, 0, 60);
    }
}
