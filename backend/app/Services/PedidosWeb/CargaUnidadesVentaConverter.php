<?php

declare(strict_types=1);

namespace App\Services\PedidosWeb;

/**
 * Conversión cantidad stock ↔ unidades de venta (CC PQ #10).
 */
final class CargaUnidadesVentaConverter
{
    public static function resolveEquivalenciaVentas(mixed $equivalenciaVentas): float
    {
        $value = (float) ($equivalenciaVentas ?? 0);

        return $value > 0 ? $value : 1.0;
    }

    /**
     * @return array{cantidad: float, cantidad_venta: float}
     */
    public static function fromCantidadUsuario(
        float $cantidadUsuario,
        mixed $equivalenciaVentas,
        bool $cargaUnidadesVenta,
    ): array {
        $equiv = self::resolveEquivalenciaVentas($equivalenciaVentas);
        $cantidadUsuario = round($cantidadUsuario, 4);

        if ($cargaUnidadesVenta) {
            $cantidadVenta = $cantidadUsuario;
            $cantidad = round($cantidadVenta * $equiv, 4);
        } else {
            $cantidad = $cantidadUsuario;
            $cantidadVenta = round($cantidad / $equiv, 4);
        }

        return [
            'cantidad' => $cantidad,
            'cantidad_venta' => $cantidadVenta,
        ];
    }

    /**
     * Cantidad a mostrar al usuario / mail según el parámetro.
     */
    public static function cantidadVisibleParaUsuario(
        float $cantidad,
        float $cantidadVenta,
        bool $cargaUnidadesVenta,
    ): float {
        return $cargaUnidadesVenta ? $cantidadVenta : $cantidad;
    }

    /**
     * Completa el par cantidad/cantidad_venta si falta uno.
     *
     * @param  array<string, mixed>  $renglon
     * @return array{cantidad: float, cantidad_venta: float}
     */
    public static function ensurePair(
        array $renglon,
        mixed $equivalenciaVentas = 1,
        bool $cargaUnidadesVenta = false,
    ): array {
        $hasCantidad = array_key_exists('cantidad', $renglon) && $renglon['cantidad'] !== null && $renglon['cantidad'] !== '';
        $hasCantidadVenta = array_key_exists('cantidad_venta', $renglon)
            || array_key_exists('cantidadVenta', $renglon);

        $cantidadVentaRaw = $renglon['cantidad_venta'] ?? $renglon['cantidadVenta'] ?? null;

        if ($hasCantidad && $hasCantidadVenta && $cantidadVentaRaw !== null && $cantidadVentaRaw !== '') {
            return [
                'cantidad' => round((float) $renglon['cantidad'], 4),
                'cantidad_venta' => round((float) $cantidadVentaRaw, 4),
            ];
        }

        if ($hasCantidad && ! $hasCantidadVenta) {
            return self::fromCantidadUsuario((float) $renglon['cantidad'], $equivalenciaVentas, false);
        }

        if (! $hasCantidad && $hasCantidadVenta) {
            return self::fromCantidadUsuario((float) $cantidadVentaRaw, $equivalenciaVentas, true);
        }

        $cantidadUsuario = (float) ($renglon['cantidad'] ?? $cantidadVentaRaw ?? 0);

        return self::fromCantidadUsuario($cantidadUsuario, $equivalenciaVentas, $cargaUnidadesVenta);
    }

    /**
     * Recalcula el par según el parámetro vigente.
     * Con CargaUnidadesVenta=true la cantidad de usuario es cantidad_venta;
     * no se confía en un `cantidad` (stock) potencialmente obsoleto.
     *
     * @param  array<string, mixed>  $renglon
     * @return array{cantidad: float, cantidad_venta: float}
     */
    public static function materializeSegunParametro(
        array $renglon,
        mixed $equivalenciaVentas,
        bool $cargaUnidadesVenta,
    ): array {
        if ($cargaUnidadesVenta) {
            $cantidadVentaRaw = $renglon['cantidad_venta'] ?? $renglon['cantidadVenta'] ?? null;
            $cantidadUsuario = ($cantidadVentaRaw !== null && $cantidadVentaRaw !== '')
                ? (float) $cantidadVentaRaw
                : (float) ($renglon['cantidad'] ?? 0);

            return self::fromCantidadUsuario($cantidadUsuario, $equivalenciaVentas, true);
        }

        return self::fromCantidadUsuario((float) ($renglon['cantidad'] ?? 0), $equivalenciaVentas, false);
    }
}
