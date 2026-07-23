<?php

namespace App\Services\PedidosWeb\CargaAsistente;

/**
 * Formato de filas/totales para consultas F–G del asistente de carga.
 */
final class CargaAsistenteConsultaFormatting
{
    public static function formatDateOnly(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', (string) $value, $matches) === 1) {
            return $matches[1];
        }

        return (string) $value;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, float>
     */
    public static function totalsIfMultiple(array $items, string $amountKey): array
    {
        if (count($items) <= 1) {
            return [];
        }

        $sum = 0.0;
        foreach ($items as $item) {
            $sum += (float) ($item[$amountKey] ?? 0);
        }

        return [$amountKey => round($sum, 2)];
    }
}
