<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use App\Services\PedidosWeb\Integracion\Export\Dto\ExportComprobantesItem;
use Illuminate\Support\Carbon;

final class IntegracionExportComprobanteMapper
{
    /**
     * @param  list<object>  $cabeceraRows
     * @param  list<object>  $renglonRows
     * @return list<ExportComprobantesItem>
     */
    public function assembleItems(array $cabeceraRows, array $renglonRows): array
    {
        $renglonesByPedido = [];
        foreach ($renglonRows as $row) {
            $codPedido = (string) ($row->cod_pedido ?? '');
            $renglonesByPedido[$codPedido][] = $this->mapRenglonRow($row);
        }

        $items = [];
        foreach ($cabeceraRows as $row) {
            $codPedido = (string) ($row->cod_pedido ?? '');
            $items[] = new ExportComprobantesItem(
                $this->mapCabeceraRow($row),
                $renglonesByPedido[$codPedido] ?? [],
                $this->mapCierreRow($row),
            );
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function mapCabeceraRow(object $row): array
    {
        $cabecera = [
            'codPedido' => (string) ($row->cod_pedido ?? ''),
            'codCliente' => (string) ($row->cod_cliente ?? ''),
            'razonSocial' => (string) ($row->cliente_nombre ?? ''),
            'nombreFantasia' => $this->resolveNombreFantasia($row),
            'fecha' => $this->formatDateTime($row->fecha ?? null),
            'nivel' => isset($row->nivel) ? (int) $row->nivel : null,
            'observaciones' => (string) ($row->observaciones ?? ''),
            'incluyeIva' => filter_var($row->incluye_iva ?? false, FILTER_VALIDATE_BOOLEAN),
            'moneda' => (int) ($row->moneda ?? 1),
            'estado' => (int) ($row->estado ?? 0),
            'fechaModif' => $this->formatDateTime($row->fecha_modif ?? null),
            'total' => (float) ($row->total ?? 0),
            'totalIva' => (float) ($row->total_iva ?? 0),
            'leyenda1' => (string) ($row->leyenda_1 ?? ''),
            'leyenda2' => (string) ($row->leyenda_2 ?? ''),
            'leyenda3' => (string) ($row->leyenda_3 ?? ''),
            'leyenda4' => (string) ($row->leyenda_4 ?? ''),
            'leyenda5' => (string) ($row->leyenda_5 ?? ''),
            'descuento' => (float) ($row->descuento ?? 0),
            'bonif1' => (float) ($row->bonif_1 ?? 0),
            'bonif2' => (float) ($row->bonif_2 ?? 0),
            'bonif3' => (float) ($row->bonif_3 ?? 0),
            'codPerfil' => (string) ($row->cod_perfil ?? ''),
            'perfilDescripcion' => (string) ($row->perfil_descripcion ?? ''),
            'codVended' => (string) ($row->cod_vended ?? ''),
            'vendedorDescripcion' => (string) ($row->vendedor_nombre ?? ''),
            'codCondvta' => isset($row->cod_condvta) ? (int) $row->cod_condvta : null,
            'condicionVentaDescripcion' => (string) ($row->condventa_descripcion ?? ''),
            'idDe' => isset($row->id_de) ? (int) $row->id_de : null,
            'direccionEntregaDescripcion' => (string) ($row->direccion_entrega ?? ''),
            'codTranspor' => (string) ($row->cod_transpor ?? ''),
            'transporteDescripcion' => (string) ($row->transporte_descripcion ?? ''),
            'listaPrecios' => isset($row->lista_precios) ? (int) $row->lista_precios : null,
            'listaPreciosDescripcion' => (string) ($row->listaprecios_descripcion ?? ''),
            'expreso' => (string) ($row->expreso ?? ''),
            'expresoDire' => (string) ($row->expreso_dire ?? ''),
            'fechaEntrega' => $this->formatDateTime($row->fecha_entrega ?? null),
            'usuarioCreacion' => (string) ($row->usuario_creacion ?? ''),
            'fechaCreacion' => $this->formatDateTime($row->fecha_creacion ?? null),
            'usuarioModificacion' => (string) ($row->usuario_modificacion ?? ''),
            'fechahoraInicioProceso' => $this->formatDateTime($row->fechahora_inicio_proceso ?? null),
            'fechahoraUltimaActividad' => $this->formatDateTime($row->fechahora_ultima_actividad ?? null),
            'numeroVisible' => (int) ($row->nro_visible ?? 0),
            'talPedidoTango' => isset($row->tal_pedido_tango) ? (int) $row->tal_pedido_tango : 0,
            'nroPedidoTango' => (string) ($row->nro_pedido_tango ?? ''),
            'codUsuarioWeb' => (string) ($row->cod_usuario_web ?? ''),
        ];

        if (filled($row->origen_comprobante ?? null)) {
            $cabecera['origenComprobante'] = (string) $row->origen_comprobante;
        }
        if (filled($row->cod_pedido_origen ?? null)) {
            $cabecera['codPedidoOrigen'] = (string) $row->cod_pedido_origen;
        }
        if (filled($row->cod_presupuesto_origen ?? null)) {
            $cabecera['codPresupuestoOrigen'] = (string) $row->cod_presupuesto_origen;
        }

        return $cabecera;
    }

    /**
     * @return array<string, mixed>
     */
    public function mapRenglonRow(object $row): array
    {
        $porcBonif = $row->porc_bonif ?? $row->bonificacion ?? 0;
        $precioLista = $row->precio ?? $row->importe_lista ?? 0;

        return [
            'renglon' => (int) ($row->renglon ?? 0),
            'codArticulo' => (string) ($row->cod_articulo ?? ''),
            'descripcionArticulo' => $this->resolveDescripcionArticulo($row),
            'especial' => $this->resolveEspecial($row),
            'cantidad' => (float) ($row->cantidad ?? 0),
            'cantidadVenta' => (float) ($row->cantidad_venta ?? $row->cantidad ?? 0),
            'porcBonif' => (float) $porcBonif,
            'precioLista' => (float) $precioLista,
            'precioNeto' => (float) ($row->precio_neto ?? 0),
            'importeBruto' => (float) ($row->precio_bruto ?? 0),
            'importeNeto' => (float) ($row->importe_neto ?? 0),
            'ivaNeto' => (float) ($row->iva ?? 0),
            'importeNetoConIva' => (float) ($row->importe_total ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function mapCierreRow(object $row): ?array
    {
        if ((int) ($row->estado ?? 0) !== 98) {
            return null;
        }

        if (! filled($row->cierre_tipo_cierre ?? null)) {
            return null;
        }

        return [
            'tipoCierre' => (string) $row->cierre_tipo_cierre,
            'idMotivo' => isset($row->cierre_id_motivo) ? (int) $row->cierre_id_motivo : null,
            'motivoDescripcion' => (string) ($row->cierre_motivo_descripcion ?? ''),
            'fechaCierre' => $this->formatDateTime($row->cierre_fecha_cierre ?? null),
            'codPedidoGenerado' => filled($row->cierre_cod_pedido_generado ?? null)
                ? (string) $row->cierre_cod_pedido_generado
                : null,
            'observacion' => (string) ($row->cierre_observacion ?? ''),
        ];
    }

    private function resolveNombreFantasia(object $row): string
    {
        $fantasia = trim((string) ($row->cliente_fantasia ?? ''));

        if ($fantasia !== '') {
            return $fantasia;
        }

        return (string) ($row->cliente_nombre ?? '');
    }

    private function resolveDescripcionArticulo(object $row): string
    {
        if (filled($row->descripcion_articulo ?? null)) {
            return (string) $row->descripcion_articulo;
        }

        return (string) ($row->articulo_descripcion ?? '');
    }

    private function resolveEspecial(object $row): string
    {
        if (! isset($row->articulo_especial)) {
            return '';
        }

        return filter_var($row->articulo_especial, FILTER_VALIDATE_BOOLEAN) ? '*' : '';
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toIso8601String();
    }
}