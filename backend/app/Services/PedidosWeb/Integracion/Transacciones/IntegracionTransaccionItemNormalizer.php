<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

final class IntegracionTransaccionItemNormalizer
{
    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function normalize(string $recurso, array $item): array
    {
        return match ($recurso) {
            'historial-ventas' => self::normalizeHistorialVentas($item),
            'deuda' => self::normalizeDeudaFechas($item),
            default => $item,
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function normalizeHistorialVentas(array $item): array
    {
        $item['tipo'] ??= $item['tComp'] ?? null;
        $item['numero'] ??= $item['nComp'] ?? null;
        $item['codArticulo'] ??= $item['codArticu'] ?? null;
        $item['razonSocial'] ??= $item['razonSoci'] ?? null;
        $item['descripcion'] ??= $item['descripcio'] ?? null;
        $item['totSinImp'] ??= $item['totSImp'] ?? null;
        $item['fechaEmision'] ??= $item['fechaEmi'] ?? null;

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function normalizeDeudaFechas(array $item): array
    {
        foreach (['fechaEmis', 'fechaVto'] as $field) {
            if (! array_key_exists($field, $item) || $item[$field] === null || $item[$field] === '') {
                continue;
            }
            $item[$field] = self::normalizeDateTimeValue($item[$field]);
        }

        return $item;
    }

    private static function normalizeDateTimeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d\TH:i:s');
        }

        if (is_string($value) && str_contains($value, 'T')) {
            return $value;
        }

        return $value;
    }
}
