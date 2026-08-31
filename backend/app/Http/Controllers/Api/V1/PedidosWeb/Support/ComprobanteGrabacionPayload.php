<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb\Support;

use Illuminate\Http\Request;

final class ComprobanteGrabacionPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function fromRequest(Request $request, string $accionGrabacion, ?string $codPedido = null): array
    {
        $request->validate(self::rules());

        return [
            'accionGrabacion' => $accionGrabacion,
            'cod_pedido' => $codPedido ?? $request->input('cod_pedido'),
            'cod_pedido_origen' => $request->input('cod_pedido_origen'),
            'cod_presupuesto_origen' => $request->input('cod_presupuesto_origen'),
            'cod_comprobante_origen_copia' => $request->input('cod_comprobante_origen_copia'),
            'cabecera' => (array) $request->input('cabecera', []),
            'renglones' => (array) $request->input('renglones', []),
            'leyendas_dirty' => (array) $request->input('leyendas_dirty', []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function rules(): array
    {
        return [
            'accionGrabacion' => ['sometimes', 'string', 'in:pedido,presupuesto'],
            'cod_pedido' => ['nullable', 'string'],
            'cod_pedido_origen' => ['nullable', 'string'],
            'cod_presupuesto_origen' => ['nullable', 'string'],
            'cod_comprobante_origen_copia' => ['nullable', 'string'],
            'cabecera' => ['required', 'array'],
            'cabecera.cod_cliente' => ['required', 'string'],
            'renglones' => ['required', 'array', 'min:1'],
            'leyendas_dirty' => ['sometimes', 'array'],
            'leyendas_dirty.leyenda_1_dirty' => ['sometimes', 'boolean'],
            'leyendas_dirty.leyenda_2_dirty' => ['sometimes', 'boolean'],
            'leyendas_dirty.leyenda_3_dirty' => ['sometimes', 'boolean'],
            'leyendas_dirty.leyenda_4_dirty' => ['sometimes', 'boolean'],
            'leyendas_dirty.leyenda_5_dirty' => ['sometimes', 'boolean'],
        ];
    }
}
