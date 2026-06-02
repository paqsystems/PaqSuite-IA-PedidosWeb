import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../../consultas/components/ConsultaGridPage';
import { fetchPedidosPendientes, type PedidoConsultaRow } from '../../consultas/api/consultaApi';
import type { DataGridRowAction } from '../../../shared/ui/grids';

const proceso = 'pw_pedidospendientes';
const gridId = 'pw_pedidospendientes';

export function PedidosPendientesPage() {
  const { t } = useTranslation();

  const loadData = useCallback(() => fetchPedidosPendientes(), []);

  const rowActions: DataGridRowAction<PedidoConsultaRow>[] = [
    {
      actionKey: 'ver',
      icon: 'find',
      hintKey: 'grid.action.view',
      onClick: () => undefined,
    },
  ];

  return (
    <ConsultaGridPage<PedidoConsultaRow>
      pageTestId="page-pedidos-pendientes"
      pageTitleKey="pages.pedidosPendientes"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={rowActions}
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
