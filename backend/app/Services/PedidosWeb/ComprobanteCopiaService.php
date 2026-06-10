<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;

final class ComprobanteCopiaService
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function copiarBorrador(string $codComprobanteOrigen, string $tipoDestino): array
    {
        $comprobante = $this->pedidoRepository->findWithDetalle($codComprobanteOrigen);

        if ($comprobante === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        return [
            'cabecera' => [
                'cod_cliente' => (string) $comprobante->cod_cliente,
                'nivel' => (int) $comprobante->nivel,
                'observaciones' => $comprobante->observaciones,
                'incluye_iva' => (bool) $comprobante->incluye_iva,
                'moneda' => (int) $comprobante->moneda,
                'cod_vended' => $comprobante->cod_vended,
                'cod_condvta' => $comprobante->cod_condvta,
                'cod_transpor' => $comprobante->cod_transpor,
                'lista_precios' => $comprobante->lista_precios,
                'descuento' => (float) $comprobante->descuento,
            ],
            'renglones' => $comprobante->detalles
                ->map(static fn ($detalle): array => [
                    'renglon' => (int) $detalle->renglon,
                    'cod_articulo' => (string) $detalle->cod_articulo,
                    'descripcion_articulo' => (string) ($detalle->descripcion_articulo ?? ''),
                    'cantidad' => (float) $detalle->cantidad,
                    'porc_bonif' => (float) $detalle->porc_bonif,
                    'precio' => (float) $detalle->precio,
                    'porc_iva' => (float) $detalle->porc_iva,
                ])
                ->values()
                ->all(),
            'tipoComprobante' => $tipoDestino,
            'codComprobanteOrigen' => $codComprobanteOrigen,
        ];
    }
}
