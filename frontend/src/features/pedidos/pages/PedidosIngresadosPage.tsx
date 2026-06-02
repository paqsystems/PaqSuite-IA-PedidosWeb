import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../../consultas/components/ConsultaGridPage';
import { useComprobanteConsultaActions } from '../../consultas/hooks/useComprobanteConsultaActions';
import { fetchPedidosIngresados, type PedidoConsultaRow } from '../../consultas/api/consultaApi';
import type { DataGridRowAction } from '../../../shared/ui/grids';

const proceso = 'pw_pedidosingresados';
const gridId = 'pw_pedidosingresados';

export function PedidosIngresadosPage() {
  const { t } = useTranslation();
  const [refreshToken, setRefreshToken] = useState(0);
  const reloadGrid = useCallback(() => {
    setRefreshToken((value) => value + 1);
  }, []);
  const { openCarga, handleCopiar, handleEliminarPedido } = useComprobanteConsultaActions({
    onChanged: reloadGrid,
  });
  const loadData = useCallback(() => fetchPedidosIngresados(), []);

  const rowActions: DataGridRowAction<PedidoConsultaRow>[] = useMemo(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: (row) => {
          openCarga(row, 'ver');
        },
      },
      {
        actionKey: 'editar',
        icon: 'edit',
        hintKey: 'grid.action.edit',
        visible: (row) => row.puedeEditar,
        onClick: (row) => {
          openCarga(row, 'editar');
        },
      },
      {
        actionKey: 'eliminar',
        icon: 'trash',
        hintKey: 'grid.action.delete',
        visible: (row) => row.puedeEliminar,
        onClick: (row) => {
          void handleEliminarPedido(row);
        },
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: (row) => row.puedeCopiar,
        onClick: (row) => {
          handleCopiar(row);
        },
      },
    ],
    [handleCopiar, handleEliminarPedido, openCarga],
  );

  return (
    <ConsultaGridPage<PedidoConsultaRow>
      pageTestId="page-pedidos-ingresados"
      pageTitleKey="pages.pedidosIngresados"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={rowActions}
      refreshToken={refreshToken}
      columns={
        <>
          <Column dataField="numero" caption={t('consultas.column.numero')} />
          <Column dataField="cliente" caption={t('consultas.column.cliente')} />
          <Column dataField="estado" caption={t('consultas.column.estado')} />
          <Column dataField="importe" caption={t('consultas.column.importe')} dataType="number" format="currency" />
        </>
      }
    />
  );
}
