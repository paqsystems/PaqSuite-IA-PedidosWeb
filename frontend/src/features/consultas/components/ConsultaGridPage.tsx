import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { DataGridDx, type DataGridDxHandle, type DataGridRowAction } from '../../../shared/ui/grids';
import type { ConsultaMeta } from '../api/consultaApi';

type BaseRow = Record<string, unknown> & {
  id: string;
};

type ConsultaGridPageProps<TRecord extends BaseRow> = {
  pageTestId: string;
  pageTitleKey: string;
  proceso: string;
  gridId: string;
  loadData: () => Promise<{ items: TRecord[]; meta: ConsultaMeta | null }>;
  columns: React.ReactNode;
  rowActions: DataGridRowAction<TRecord>[];
  refreshToken?: number;
};

export function ConsultaGridPage<TRecord extends BaseRow>({
  pageTestId,
  pageTitleKey,
  proceso,
  gridId,
  loadData,
  columns,
  rowActions,
  refreshToken = 0,
}: ConsultaGridPageProps<TRecord>) {
  const { t } = useTranslation();
  const gridRef = useRef<DataGridDxHandle>(null);
  const { toolbar: layoutToolbar, saveAsDialog } = useGridLayouts({
    proceso,
    gridId,
    gridRef,
  });
  const [rows, setRows] = useState<TRecord[]>([]);
  const [meta, setMeta] = useState<ConsultaMeta | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      setLoadError(null);
      try {
        const result = await loadData();
        if (!mounted) {
          return;
        }

        setRows(result.items);
        setMeta(result.meta);
      } catch {
        if (!mounted) {
          return;
        }

        setRows([]);
        setMeta(null);
        setLoadError(t('grid.error.load'));
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [loadData, refreshToken, t]);

  return (
    <section data-testid={pageTestId}>
      <h2>{t(pageTitleKey)}</h2>
      <p>{t('consultas.fechaProceso', { value: meta?.fecha_proceso ?? t('consultas.fechaProcesoSinDato') })}</p>
      <DataGridDx<TRecord>
        ref={gridRef}
        proceso={proceso}
        gridId={gridId}
        dataSource={rows}
        keyExpr="id"
        isLoading={isLoading}
        loadError={loadError}
        toolbarEnd={layoutToolbar}
        rowActions={rowActions}
      >
        <Column dataField="id" visible={false} />
        {columns}
      </DataGridDx>
      {saveAsDialog}
    </section>
  );
}
