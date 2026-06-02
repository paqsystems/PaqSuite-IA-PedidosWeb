import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Tabs from 'devextreme-react/tabs';
import { Column } from 'devextreme-react/data-grid';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { DataGridDx, type DataGridDxHandle, type DataGridRowAction } from '../../../shared/ui/grids';
import { fetchPresupuestosActivos, fetchPresupuestosCerrados, type PresupuestoConsultaRow } from '../../consultas/api/consultaApi';

const presupuestosTabItems = [
  { id: 'activos', labelKey: 'presupuestos.tabs.activos' },
  { id: 'cerrados', labelKey: 'presupuestos.tabs.cerrados' },
] as const;

type PresupuestosTabId = (typeof presupuestosTabItems)[number]['id'];

function resolveProceso(tabId: PresupuestosTabId): string {
  return tabId === 'activos' ? 'pw_presupuestosactivos' : 'pw_presupuestoscerrados';
}

export function PresupuestosPage() {
  const { t } = useTranslation();
  const gridRef = useRef<DataGridDxHandle>(null);
  const [activeTabId, setActiveTabId] = useState<PresupuestosTabId>('activos');
  const [rows, setRows] = useState<PresupuestoConsultaRow[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);

  const proceso = resolveProceso(activeTabId);
  const gridId = proceso;

  const { toolbar: layoutToolbar, saveAsDialog } = useGridLayouts({
    proceso,
    gridId,
    gridRef,
  });

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      setLoadError(null);
      try {
        const result =
          activeTabId === 'activos' ? await fetchPresupuestosActivos() : await fetchPresupuestosCerrados();
        if (!mounted) {
          return;
        }

        setRows(result.items);
      } catch {
        if (mounted) {
          setRows([]);
          setLoadError(t('grid.error.load'));
        }
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
  }, [activeTabId, t]);

  const tabLabels = useMemo(() => presupuestosTabItems.map((item) => t(item.labelKey)), [t]);
  const activeTabIndex = activeTabId === 'activos' ? 0 : 1;

  const rowActions: DataGridRowAction<PresupuestoConsultaRow>[] = useMemo(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: () => undefined,
      },
      {
        actionKey: 'editar',
        icon: 'edit',
        hintKey: 'grid.action.edit',
        visible: () => activeTabId === 'activos',
        onClick: () => undefined,
      },
      {
        actionKey: 'convertir',
        icon: 'redo',
        hintKey: 'grid.action.convert',
        visible: () => activeTabId === 'activos',
        onClick: () => undefined,
      },
      {
        actionKey: 'cerrar',
        icon: 'close',
        hintKey: 'grid.action.close',
        visible: () => activeTabId === 'activos',
        onClick: () => undefined,
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: () => activeTabId === 'activos',
        onClick: () => undefined,
      },
    ],
    [activeTabId],
  );

  const handleTabChange = useCallback((tabIndex: number) => {
    setActiveTabId(tabIndex === 0 ? 'activos' : 'cerrados');
  }, []);

  return (
    <section data-testid="page-presupuestos-ingresados">
      <h2>{t('pages.presupuestosIngresados')}</h2>
      <Tabs
        dataSource={tabLabels}
        selectedIndex={activeTabIndex}
        onSelectedIndexChange={(tabIndex) => handleTabChange(Number(tabIndex))}
        elementAttr={{ 'data-testid': 'presupuestos-tabs-99-98' }}
      />
      <DataGridDx<PresupuestoConsultaRow>
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
        <Column dataField="numero" caption={t('consultas.column.numero')} />
        <Column dataField="cliente" caption={t('consultas.column.cliente')} />
        <Column dataField="estado" caption={t('consultas.column.estado')} />
        <Column dataField="importe" caption={t('consultas.column.importe')} dataType="number" format="currency" />
      </DataGridDx>
      {saveAsDialog}
    </section>
  );
}
