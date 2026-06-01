import { useMemo, useRef } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { DataGridDx, type DataGridDxHandle } from '../../../shared/ui/grids';

const dashboardProceso = 'pw_dashboard';
const dashboardGridId = 'main';

type DashboardDemoRow = {
  id: number;
  name: string;
};

export function DashboardPage() {
  const { t } = useTranslation();
  const gridRef = useRef<DataGridDxHandle>(null);
  const { toolbar: layoutToolbar, saveAsDialog } = useGridLayouts({
    proceso: dashboardProceso,
    gridId: dashboardGridId,
    gridRef,
  });

  const rows = useMemo<DashboardDemoRow[]>(
    () => [
      { id: 1, name: 'Alpha' },
      { id: 2, name: 'Beta' },
    ],
    [],
  );

  return (
    <section data-testid="process-dashboard">
      <h2>{t('dashboard.title')}</h2>
      <p>{t('dashboard.description')}</p>
      <p className="dashboardPage__hint">{t('grid.consulta.noAbmHint')}</p>
      <Link to="/pedidos/ingresados" data-testid="nav-pedidos-ingresados">
        {t('dashboard.linkPedidos')}
      </Link>
      <DataGridDx<DashboardDemoRow>
        ref={gridRef}
        proceso={dashboardProceso}
        gridId={dashboardGridId}
        dataSource={rows}
        keyExpr="id"
        toolbarEnd={layoutToolbar}
        rowActions={[
          {
            actionKey: 'edit',
            icon: 'edit',
            hintKey: 'grid.action.edit',
            onClick: () => undefined,
          },
        ]}
      >
        <Column dataField="id" caption={t('grid.column.id')} width={80} />
        <Column dataField="name" caption={t('grid.column.name')} />
      </DataGridDx>
      {saveAsDialog}
    </section>
  );
}
