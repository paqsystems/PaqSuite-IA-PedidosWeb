import { useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGrillaPivotShell } from '../../../shared/pivot';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { DataGridDx, type DataGridDxHandle, type DataGridRowAction } from '../../../shared/ui/grids';
import type { ConsultaMeta } from '../api/consultaApi';
import { GridRefreshButton } from './GridRefreshButton';
import { formatConsultaFechaProceso } from '../utils/formatConsultaFechaProceso';

type BaseRow = Record<string, unknown> & {
  id: string;
};

type ConsultaInformePivotPageProps<TRecord extends BaseRow> = {
  pageTestId: string;
  pageTitleKey: string;
  proceso: string;
  gridId: string;
  pivotConsultaId: string;
  testIdPrefix: string;
  loadData: () => Promise<{ items: TRecord[]; meta: ConsultaMeta | null }>;
  columns: React.ReactNode;
  rowActions?: DataGridRowAction<TRecord>[];
  headerExtra?: React.ReactNode;
  enableDrillDown?: boolean;
};

export function ConsultaInformePivotPage<TRecord extends BaseRow>({
  pageTestId,
  pageTitleKey,
  proceso,
  gridId,
  pivotConsultaId,
  testIdPrefix,
  loadData,
  columns,
  rowActions = [],
  headerExtra,
  enableDrillDown = false,
}: ConsultaInformePivotPageProps<TRecord>) {
  const { t, i18n } = useTranslation();
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
  const [refreshToken, setRefreshToken] = useState(0);
  const [drillDownFilters, setDrillDownFilters] = useState<Record<string, unknown> | null>(null);

  const fechaProcesoLabel = useMemo(() => {
    const rawValue = meta?.fecha_proceso;

    if (!rawValue) {
      return t('consultas.fechaProcesoSinDato');
    }

    return formatConsultaFechaProceso(rawValue, i18n.language);
  }, [i18n.language, meta?.fecha_proceso, t]);

  const toolbarEnd = useMemo(
    () => (
      <>
        <GridRefreshButton onRefresh={() => setRefreshToken((value) => value + 1)} />
        {layoutToolbar}
      </>
    ),
    [layoutToolbar],
  );

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

  const visibleRows = useMemo(() => {
    if (!enableDrillDown || !drillDownFilters) {
      return rows;
    }

    return rows.filter((row) =>
      Object.entries(drillDownFilters).every(([field, value]) => {
        const rowValue = row[field];
        return String(rowValue ?? '') === String(value ?? '');
      }),
    );
  }, [drillDownFilters, enableDrillDown, rows]);

  return (
    <section data-testid={pageTestId}>
      <h2>{t(pageTitleKey)}</h2>
      <p>{t('consultas.fechaProceso', { value: fechaProcesoLabel })}</p>
      {headerExtra}
      <ConsultaGrillaPivotShell
        consultaId={pivotConsultaId}
        tipoProceso="informe"
        testIdPrefix={testIdPrefix}
        refreshToken={refreshToken}
        onRefresh={() => setRefreshToken((value) => value + 1)}
        onDrillDownFilters={enableDrillDown ? setDrillDownFilters : undefined}
        gridContent={
          (
            <DataGridDx<TRecord>
              ref={gridRef}
              proceso={proceso}
              gridId={gridId}
              dataSource={visibleRows}
              keyExpr="id"
              isLoading={isLoading}
              loadError={loadError}
              toolbarEnd={toolbarEnd}
              rowActions={rowActions}
            >
              <Column dataField="id" visible={false} />
              {columns}
            </DataGridDx>
          ) as ReactNode
        }
      />
      {saveAsDialog}
    </section>
  );
}
