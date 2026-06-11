<?php

namespace App\Services\Pivots;

/**
 * Regla transversal: todo campo es totalizable; las agregaciones válidas dependen solo del tipo de dato.
 * El catálogo no define listas por campo (agregaciones_permitidas_json queda obsoleto en metadata).
 */
final class PivotCampoAggregationPolicy
{
    /** @var list<string> */
    private const NUMERIC_AGGREGATIONS = ['sum', 'avg', 'min', 'max', 'count'];

    /** @var list<string> */
    private const DISCRETE_AGGREGATIONS = ['count', 'min', 'max'];

    /**
     * @return list<string>
     */
    public static function resolveAgregacionesPermitidas(string $tipoDato): array
    {
        return self::isDiscreteTipoDato($tipoDato)
            ? self::DISCRETE_AGGREGATIONS
            : self::NUMERIC_AGGREGATIONS;
    }

    public static function resolveAgregacionDefault(string $tipoDato, ?string $preferida = null): string
    {
        $permitidas = self::resolveAgregacionesPermitidas($tipoDato);

        if ($preferida !== null && $preferida !== '' && in_array($preferida, $permitidas, true)) {
            return $preferida;
        }

        return self::isDiscreteTipoDato($tipoDato) ? 'count' : 'sum';
    }

    /**
     * Todo campo puede ubicarse en valores (totalización).
     *
     * @param  list<string>  $rolesPermitidos
     * @return list<string>
     */
    public static function normalizeRolesPermitidos(array $rolesPermitidos): array
    {
        if (! in_array('valor', $rolesPermitidos, true)) {
            $rolesPermitidos[] = 'valor';
        }

        return array_values(array_unique($rolesPermitidos));
    }

    private static function isDiscreteTipoDato(string $tipoDato): bool
    {
        $normalized = strtolower(trim($tipoDato));

        return in_array($normalized, ['string', 'date', 'datetime'], true);
    }
}
