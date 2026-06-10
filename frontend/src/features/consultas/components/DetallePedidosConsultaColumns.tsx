import { Column } from 'devextreme-react/data-grid';
import type { TFunction } from 'i18next';

const decimalColumnProps = {
  dataType: 'number' as const,
  format: '#,##0.00',
};

const percentColumnProps = {
  dataType: 'number' as const,
  format: '#,##0.00',
};

type DetallePedidosConsultaColumnsProps = {
  t: TFunction;
};

export function DetallePedidosConsultaColumns({ t }: DetallePedidosConsultaColumnsProps) {
  return (
    <>
      <Column dataField="renglon" caption={t('consultas.detalle.column.renglon')} dataType="number" visible />
      <Column dataField="codArticulo" caption={t('consultas.detalle.column.codArticulo')} visible />
      <Column dataField="descripcionArticulo" caption={t('consultas.detalle.column.descripcionArticulo')} visible />
      <Column dataField="cantidad" caption={t('consultas.detalle.column.cantidad')} {...decimalColumnProps} visible />
      <Column dataField="porcBonif" caption={t('consultas.detalle.column.descuento')} {...percentColumnProps} visible />
      <Column dataField="precioLista" caption={t('consultas.detalle.column.precioLista')} {...decimalColumnProps} visible />
      <Column dataField="precioNeto" caption={t('consultas.detalle.column.precioNeto')} {...decimalColumnProps} visible />
      <Column dataField="importeBruto" caption={t('consultas.detalle.column.importeBruto')} {...decimalColumnProps} visible />
      <Column dataField="importeNeto" caption={t('consultas.detalle.column.importeNeto')} {...decimalColumnProps} visible />
      <Column dataField="ivaNeto" caption={t('consultas.detalle.column.ivaNeto')} {...decimalColumnProps} visible />
      <Column
        dataField="importeNetoConIva"
        caption={t('consultas.detalle.column.importeNetoConIva')}
        {...decimalColumnProps}
        visible
      />
    </>
  );
}
