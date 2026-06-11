<?php

namespace App\Services\Pivots;

/**
 * Fuente de verdad: formato decimal en pivot (paridad grillas GEN-03).
 * Aplica a valores y totalizadores DevExtreme (field.format en área data).
 */
final class PivotCampoFormatPolicy
{
    public const DECIMAL_DX_FORMAT = '#,##0.00';

    /**
     * @param  array<string, mixed>|null  $formato
     * @return array<string, mixed>|null
     */
    public static function resolveFormato(?array $formato, string $tipoDato): ?array
    {
        if (self::isNumericTipoDato($tipoDato)) {
            return ['format' => self::DECIMAL_DX_FORMAT];
        }

        return $formato;
    }

    public static function isNumericTipoDato(string $tipoDato): bool
    {
        $normalized = strtolower(trim($tipoDato));

        return in_array($normalized, ['number', 'numeric', 'decimal'], true);
    }
}
