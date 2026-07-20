import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import Switch from 'devextreme-react/switch';
import { DataGridDx, type DataGridRowAction } from '../../../../shared/ui/grids';
import type { BorradorFila } from '../types/importacionMasivaTypes';

type ImportacionMasivaGridRow = BorradorFila & { id: string };

type ImportacionMasivaGridProps = {
  filas: ImportacionMasivaGridRow[];
  disabled: boolean;
  onToggleTipo: (idInterno: string, esPedido: boolean) => void;
  onConsultar: (fila: BorradorFila) => void;
  onEliminar: (fila: BorradorFila) => void;
};

export function ImportacionMasivaGrid({
  filas,
  disabled,
  onToggleTipo,
  onConsultar,
  onEliminar,
}: ImportacionMasivaGridProps) {
  const { t } = useTranslation();

  const rowActions = useMemo<DataGridRowAction<ImportacionMasivaGridRow>[]>(
    () => [
      {
        actionKey: 'consultar',
        icon: 'find',
        hintKey: 'pedidos.importacionMasiva.consultar',
        onClick: (row) => onConsultar(row),
      },
      {
        actionKey: 'eliminar',
        icon: 'trash',
        hintKey: 'pedidos.importacionMasiva.eliminar',
        onClick: (row) => onEliminar(row),
      },
    ],
    [onConsultar, onEliminar],
  );

  return (
    <DataGridDx<ImportacionMasivaGridRow>
      proceso="pw_importacionmasiva"
      gridId="importacionMasivaBorrador"
      dataSource={filas}
      keyExpr="id"
      isLoading={disabled}
      exportEnabled={false}
      enableGrouping={false}
      emptyMessageKey="pedidos.importacionMasiva.gridVacia"
      rowActions={rowActions}
    >
      <Column dataField="cabecera.codCliente" caption={t('pedidos.importacionMasiva.columnCliente')} />
      <Column
        dataField="cabecera.vendedorNombre"
        caption={t('pedidos.importacionMasiva.columnVendedor')}
      />
      <Column dataField="cabecera.nivel" caption={t('pedidos.importacionMasiva.columnNivel')} dataType="number" />
      <Column
        caption={t('pedidos.importacionMasiva.columnTipo')}
        width={150}
        cellRender={({ data }) => (
          <Switch
            value={data.esPedido}
            disabled={disabled}
            switchedOnText={t('pedidos.importacionMasiva.tipoPedido')}
            switchedOffText={t('pedidos.importacionMasiva.tipoPresupuesto')}
            onValueChanged={(event) => onToggleTipo(data.idInterno, Boolean(event.value))}
            elementAttr={{ 'data-testid': `importacionMasivaToggleTipo-${data.idInterno}` }}
          />
        )}
      />
      <Column
        dataField="cantidadRenglones"
        caption={t('pedidos.importacionMasiva.columnRenglones')}
        dataType="number"
      />
      <Column
        dataField="totalImporte"
        caption={t('pedidos.importacionMasiva.columnTotal')}
        dataType="number"
        format="#,##0.00"
      />
      <Column dataField="errorGrabacion" caption={t('pedidos.importacionMasiva.columnError')} />
    </DataGridDx>
  );
}
