<?php

namespace App\Services\PedidosWeb;

final class CalculoTotalesService
{
    /**
     * @param  list<array<string, mixed>>  $renglones
     * @return array{renglones: list<array<string, mixed>>, total: float, totalIva: float}
     */
    public function calcular(array $renglones, float $bonificacionNetaCabecera = 0.0): array
    {
        $renglonesCalculados = [];
        $total = 0.0;
        $totalIva = 0.0;
        $factorCabecera = 1 - ($bonificacionNetaCabecera / 100);

        foreach ($renglones as $index => $renglon) {
            $cantidad = $this->toFloat($renglon['cantidad'] ?? 0);
            $precio = $this->toFloat($renglon['precio'] ?? 0);
            $porcBonif = $this->toFloat($renglon['porc_bonif'] ?? $renglon['porcBonif'] ?? 0);
            $porcIva = $this->toFloat($renglon['porc_iva'] ?? $renglon['porcIva'] ?? 0);

            $precioNeto = round($precio * (1 - ($porcBonif / 100)) * $factorCabecera, 4);
            $importeNeto = round($cantidad * $precioNeto, 2);
            $iva = round($importeNeto * ($porcIva / 100), 2);
            $importeTotal = round($importeNeto + $iva, 2);

            $total += $importeNeto;
            $totalIva += $iva;

            $renglonesCalculados[] = [
                'renglon' => (int) ($renglon['renglon'] ?? ($index + 1)),
                'cod_articulo' => (string) ($renglon['cod_articulo'] ?? $renglon['codArticulo'] ?? ''),
                'descripcion_articulo' => (string) ($renglon['descripcion_articulo'] ?? $renglon['descripcionArticulo'] ?? ''),
                'cantidad' => $cantidad,
                'porc_bonif' => $porcBonif,
                'precio' => $precio,
                'precio_neto' => $precioNeto,
                'precio_bruto' => $precio,
                'porc_iva' => $porcIva,
                'iva' => $iva,
                'importe_lista' => round($cantidad * $precio, 2),
                'importe_neto' => $importeNeto,
                'importe_total' => $importeTotal,
                'descuento_origen' => $this->toFloat($renglon['descuento_origen'] ?? 0),
                'precio_origen' => $this->toFloat($renglon['precio_origen'] ?? $precio),
            ];
        }

        return [
            'renglones' => $renglonesCalculados,
            'total' => round($total, 2),
            'totalIva' => round($totalIva, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    public function resolveBonificacionNetaCabecera(array $cabeceraPayload): float
    {
        if (array_key_exists('descuento', $cabeceraPayload) && $cabeceraPayload['descuento'] !== null && $cabeceraPayload['descuento'] !== '') {
            return round((float) $cabeceraPayload['descuento'], 2);
        }

        return $this->calcularBonificacionNeta(
            (float) ($cabeceraPayload['bonif_1'] ?? 0),
            (float) ($cabeceraPayload['bonif_2'] ?? 0),
            (float) ($cabeceraPayload['bonif_3'] ?? 0),
        );
    }

    public function calcularBonificacionNeta(float $bonif1, float $bonif2, float $bonif3): float
    {
        $factor = (1 - ($bonif1 / 100)) * (1 - ($bonif2 / 100)) * (1 - ($bonif3 / 100));

        return round((1 - $factor) * 100, 2);
    }

    private function toFloat(mixed $value): float
    {
        return round((float) $value, 4);
    }
}
