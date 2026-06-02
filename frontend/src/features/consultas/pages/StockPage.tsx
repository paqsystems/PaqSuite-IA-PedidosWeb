import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../components/ConsultaGridPage';
import { fetchStock, type StockConsultaRow } from '../api/consultaApi';
import type { DataGridRowAction } from '../../../shared/ui/grids';

const proceso = 'pw_stock';
const gridId = 'pw_stock';

export function StockPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchStock(), []);

  const rowActions: DataGridRowAction<StockConsultaRow>[] = [
    {
      actionKey: 'ver',
      icon: 'find',
      hintKey: 'grid.action.view',
      onClick: () => undefined,
    },
  ];

  return (
    <ConsultaGridPage<StockConsultaRow>
      pageTestId="page-consulta-stock"
      pageTitleKey="pages.consultaStock"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={rowActions}
      columns={
        <>
          <Column dataField="articulo" caption={t('consultas.column.articulo')} />
          <Column dataField="stockActual" caption={t('consultas.column.stockActual')} dataType="number" />
          <Column dataField="stockComprometido" caption={t('consultas.column.stockComprometido')} dataType="number" />
        </>
      }
    />
  );
}
