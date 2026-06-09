import { Column } from 'devextreme-react/data-grid';
import type { TFunction } from 'i18next';

const dateColumnProps = {
  dataType: 'date' as const,
  format: 'dd/MM/yyyy',
};

const dateTimeColumnProps = {
  dataType: 'datetime' as const,
  format: 'dd/MM/yyyy HH:mm',
};

const decimalColumnProps = {
  dataType: 'number' as const,
  format: '#,##0.00',
};

const percentColumnProps = {
  dataType: 'number' as const,
  format: '#,##0.00',
};

function monedaCustomizeText(t: TFunction) {
  return (cellInfo: { value?: number | null }) => {
    if (cellInfo.value === 0) {
      return t('pedidos.carga.moneda.extranjera');
    }

    return t('pedidos.carga.moneda.corriente');
  };
}

function incluyeIvaCustomizeText(t: TFunction) {
  return (cellInfo: { value?: boolean | null }) =>
    cellInfo.value ? t('pedidos.carga.cabecera.si') : t('pedidos.carga.cabecera.no');
}

function estadoCustomizeText(t: TFunction) {
  return (cellInfo: { value?: number | null }) => {
    const estado = cellInfo.value;

    if (estado === null || estado === undefined) {
      return '';
    }

    return t(`consultas.comprobanteEstado.${estado}`, { defaultValue: String(estado) });
  };
}

type ComprobanteConsultaColumnsProps = {
  t: TFunction;
  extraColumns?: React.ReactNode;
  estadoVisible?: boolean;
};

export function ComprobanteConsultaColumns({ t, extraColumns, estadoVisible = false }: ComprobanteConsultaColumnsProps) {
  return (
    <>
      <Column dataField="codPedido" caption={t('consultas.column.codPedido')} visible />
      <Column dataField="codCliente" caption={t('consultas.column.codCliente')} visible />
      <Column dataField="razonSocial" caption={t('consultas.column.razonSocial')} visible />
      <Column dataField="nombreFantasia" caption={t('consultas.column.nombreFantasia')} visible />
      <Column dataField="fecha" caption={t('consultas.column.fecha')} {...dateColumnProps} visible />
      <Column dataField="nivel" caption={t('consultas.column.nivel')} dataType="number" visible={false} />
      <Column dataField="observaciones" caption={t('consultas.column.observaciones')} visible={false} />
      <Column
        dataField="incluyeIva"
        caption={t('consultas.column.incluyeIva')}
        visible={false}
        customizeText={incluyeIvaCustomizeText(t)}
      />
      <Column
        dataField="moneda"
        caption={t('consultas.column.moneda')}
        visible
        customizeText={monedaCustomizeText(t)}
      />
      <Column
        dataField="estado"
        caption={t('consultas.column.estado')}
        visible={estadoVisible}
        customizeText={estadoCustomizeText(t)}
      />
      <Column dataField="fechaModif" caption={t('consultas.column.fechaModif')} {...dateTimeColumnProps} visible={false} />
      <Column dataField="total" caption={t('consultas.column.total')} {...decimalColumnProps} visible />
      <Column dataField="totalIva" caption={t('consultas.column.totalIva')} {...decimalColumnProps} visible={false} />
      <Column dataField="leyenda1" caption={t('consultas.column.leyenda1')} visible={false} />
      <Column dataField="leyenda2" caption={t('consultas.column.leyenda2')} visible={false} />
      <Column dataField="leyenda3" caption={t('consultas.column.leyenda3')} visible={false} />
      <Column dataField="leyenda4" caption={t('consultas.column.leyenda4')} visible={false} />
      <Column dataField="leyenda5" caption={t('consultas.column.leyenda5')} visible={false} />
      <Column dataField="descuento" caption={t('consultas.column.descuento')} {...percentColumnProps} visible={false} />
      <Column dataField="bonif1" caption={t('consultas.column.bonif1')} {...percentColumnProps} visible={false} />
      <Column dataField="bonif2" caption={t('consultas.column.bonif2')} {...percentColumnProps} visible={false} />
      <Column dataField="bonif3" caption={t('consultas.column.bonif3')} {...percentColumnProps} visible={false} />
      <Column dataField="codPerfil" caption={t('consultas.column.codPerfil')} visible={false} />
      <Column dataField="perfilDescripcion" caption={t('consultas.column.perfilDescripcion')} visible={false} />
      <Column dataField="codVended" caption={t('consultas.column.codVended')} visible />
      <Column dataField="vendedorDescripcion" caption={t('consultas.column.vendedorDescripcion')} visible />
      <Column dataField="codCondvta" caption={t('consultas.column.codCondvta')} dataType="number" visible={false} />
      <Column
        dataField="condicionVentaDescripcion"
        caption={t('consultas.column.condicionVentaDescripcion')}
        visible={false}
      />
      <Column dataField="idDe" caption={t('consultas.column.idDe')} dataType="number" visible={false} />
      <Column
        dataField="direccionEntregaDescripcion"
        caption={t('consultas.column.direccionEntregaDescripcion')}
        visible={false}
      />
      <Column dataField="codTranspor" caption={t('consultas.column.codTransp')} visible={false} />
      <Column dataField="transporteDescripcion" caption={t('consultas.column.transporteDescripcion')} visible={false} />
      <Column dataField="listaPrecios" caption={t('consultas.column.listaPrecios')} dataType="number" visible />
      <Column dataField="listaPreciosDescripcion" caption={t('consultas.column.listaPreciosDescripcion')} visible />
      <Column dataField="expreso" caption={t('consultas.column.expreso')} visible={false} />
      <Column dataField="expresoDire" caption={t('consultas.column.expresoDire')} visible={false} />
      <Column dataField="fechaEntrega" caption={t('consultas.column.fechaEntrega')} {...dateColumnProps} visible={false} />
      <Column dataField="usuarioCreacion" caption={t('consultas.column.usuarioCreacion')} visible={false} />
      <Column dataField="fechaCreacion" caption={t('consultas.column.fechaCreacion')} {...dateTimeColumnProps} visible={false} />
      <Column dataField="usuarioModificacion" caption={t('consultas.column.usuarioModificacion')} visible={false} />
      <Column
        dataField="fechahoraInicioProceso"
        caption={t('consultas.column.fechahoraInicioProceso')}
        {...dateTimeColumnProps}
        visible={false}
      />
      <Column
        dataField="fechahoraUltimaActividad"
        caption={t('consultas.column.fechahoraUltimaActividad')}
        {...dateTimeColumnProps}
        visible={false}
      />
      <Column dataField="numero" caption={t('consultas.column.numero')} visible={false} />
      <Column dataField="importe" caption={t('consultas.column.importe')} {...decimalColumnProps} visible={false} />
      {extraColumns}
    </>
  );
}
