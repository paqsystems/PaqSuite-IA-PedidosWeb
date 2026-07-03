<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebPedidoCabecera;

final class ComprobanteCopiaService
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly PedidosWebParameterService $parameterService,
        private readonly CalculoTotalesService $calculoTotalesService,
        private readonly ArticuloRepositoryInterface $articuloRepository,
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

        $cabecera = $this->mapCabecera($comprobante);
        $renglones = $this->mapRenglonesOrigen($comprobante);

        if ($this->parameterService->getActualizarPrecioCopia()) {
            $listaPrecios = (int) ($comprobante->lista_precios ?? 0);

            if ($listaPrecios > 0) {
                $renglones = $this->aplicarPreciosLista($renglones, $listaPrecios);
                $this->assertPreciosListaRenglonesPermitidos($renglones);

                $bonifNeta = $this->calculoTotalesService->resolveBonificacionNetaCabecera($cabecera);
                $calculado = $this->calculoTotalesService->calcular($renglones, $bonifNeta);
                $renglones = $this->limpiarMetadatosRenglones($calculado['renglones']);
            } else {
                $this->assertPreciosOrigenRenglonesPermitidos($renglones);
            }
        } else {
            $this->assertPreciosOrigenRenglonesPermitidos($renglones);
        }

        return [
            'cabecera' => $cabecera,
            'renglones' => $this->limpiarMetadatosRenglones($renglones),
            'tipoComprobante' => $tipoDestino,
            'codComprobanteOrigen' => $codComprobanteOrigen,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCabecera(PqPedidoswebPedidoCabecera $comprobante): array
    {
        return [
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
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapRenglonesOrigen(PqPedidoswebPedidoCabecera $comprobante): array
    {
        return $comprobante->detalles
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
            ->all();
    }

    /**
     * @param list<array<string, mixed>> $renglones
     *
     * @return list<array<string, mixed>>
     */
    private function aplicarPreciosLista(array $renglones, int $listaPrecios): array
    {
        return array_map(function (array $renglon) use ($listaPrecios): array {
            $codArticulo = (string) ($renglon['cod_articulo'] ?? '');
            $precioLista = $this->articuloRepository->findPrecioLista($listaPrecios, $codArticulo);

            if ($precioLista === null) {
                return array_merge($renglon, [
                    'precio' => 0.0,
                    'precioListaAusente' => true,
                ]);
            }

            return array_merge($renglon, [
                'precio' => (float) $precioLista->precio,
                'precioListaAusente' => false,
            ]);
        }, $renglones);
    }

    /**
     * @param list<array<string, mixed>> $renglones
     *
     * @return list<array<string, mixed>>
     */
    private function limpiarMetadatosRenglones(array $renglones): array
    {
        return array_map(static function (array $renglon): array {
            unset($renglon['precioListaAusente']);

            return $renglon;
        }, $renglones);
    }

    /**
     * @param list<array<string, mixed>> $renglones
     */
    private function assertPreciosListaRenglonesPermitidos(array $renglones): void
    {
        foreach ($renglones as $renglon) {
            $codArticulo = trim((string) ($renglon['cod_articulo'] ?? ''));

            if ($codArticulo === '') {
                continue;
            }

            $precioListaAusente = (bool) ($renglon['precioListaAusente'] ?? false);

            if ($precioListaAusente) {
                if (! $this->parameterService->getArticulosSinPrecio()) {
                    throw new PedidosWebBusinessException(2000, 'business.precioCeroNoPermitido', 422);
                }

                continue;
            }

            $precio = (float) ($renglon['precio'] ?? 0);

            if ($precio <= 0 && ! $this->parameterService->getArticuloPrecioCero()) {
                throw new PedidosWebBusinessException(2000, 'business.precioCeroNoPermitido', 422);
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $renglones
     */
    private function assertPreciosOrigenRenglonesPermitidos(array $renglones): void
    {
        foreach ($renglones as $renglon) {
            $codArticulo = trim((string) ($renglon['cod_articulo'] ?? ''));

            if ($codArticulo === '') {
                continue;
            }

            $precio = (float) ($renglon['precio'] ?? 0);

            if ($precio <= 0
                && ! $this->parameterService->getArticuloPrecioCero()
                && ! $this->parameterService->getArticulosSinPrecio()) {
                throw new PedidosWebBusinessException(2000, 'business.precioCeroNoPermitido', 422);
            }
        }
    }
}
