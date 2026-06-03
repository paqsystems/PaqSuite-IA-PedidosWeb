import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Tabs from 'devextreme-react/tabs';
import { Column } from 'devextreme-react/data-grid';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { ComprobanteConsultaColumns } from '../../consultas/components/ComprobanteConsultaColumns';
import { useComprobanteConsultaActions } from '../../consultas/hooks/useComprobanteConsultaActions';
import { DataGridDx, type DataGridDxHandle, type DataGridRowAction } from '../../../shared/ui/grids';
import {
  fetchPresupuestosActivos,
  fetchPresupuestosCerrados,
  type PresupuestoConsultaRow,
} from '../../consultas/api/consultaApi';
import { PresupuestoCierreDialog } from '../components/PresupuestoCierreDialog';
import { PresupuestoCierreDetalleDialog } from '../components/PresupuestoCierreDetalleDialog';

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
  const [refreshToken, setRefreshToken] = useState(0);
  const [cierreTarget, setCierreTarget] = useState<PresupuestoConsultaRow | null>(null);
  const [cierreDialogVisible, setCierreDialogVisible] = useState(false);
  const [cierreDetalleTarget, setCierreDetalleTarget] = useState<PresupuestoConsultaRow | null>(null);
  const [cierreDetalleVisible, setCierreDetalleVisible] = useState(false);

  const reloadGrid = useCallback(() => {
    setRefreshToken((value) => value + 1);
  }, []);
  const { openCarga, handleCopiar } = useComprobanteConsultaActions();

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
  }, [activeTabId, refreshToken, t]);

  const tabLabels = useMemo(() => presupuestosTabItems.map((item) => t(item.labelKey)), [t]);
  const activeTabIndex = activeTabId === 'activos' ? 0 : 1;

  const rowActions: DataGridRowAction<PresupuestoConsultaRow>[] = useMemo(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: (row) => {
          if (activeTabId === 'cerrados') {
            setCierreDetalleTarget(row);
            setCierreDetalleVisible(true);
            return;
          }

          openCarga(row, 'ver', 'presupuesto');
        },
      },
      {
        actionKey: 'editar',
        icon: 'edit',
        hintKey: 'grid.action.edit',
        visible: (row) => activeTabId === 'activos' && row.puedeEditar,
        onClick: (row) => {
          openCarga(row, 'editar', 'presupuesto');
        },
      },
      {
        actionKey: 'convertir',
        icon: 'redo',
        hintKey: 'grid.action.convert',
        visible: (row) => activeTabId === 'activos' && row.puedeConvertir,
        onClick: (row) => {
          openCarga(row, 'convertir', 'presupuesto');
        },
      },
      {
        actionKey: 'cerrar',
        icon: 'close',
        hintKey: 'grid.action.close',
        visible: (row) => activeTabId === 'activos' && row.puedeCerrar,
        onClick: (row) => {
          setCierreTarget(row);
          setCierreDialogVisible(true);
        },
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: (row) => activeTabId === 'activos' && row.puedeCopiar,
        onClick: (row) => {
          handleCopiar(row, 'presupuesto');
        },
      },
    ],
    [activeTabId, handleCopiar, openCarga],
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
        <ComprobanteConsultaColumns
          t={t}
          extraColumns={
            activeTabId === 'cerrados' ? (
              <>
                <Column dataField="cierre.motivoDescripcion" caption={t('presupuestos.cierreDetalle.motivo')} />
                <Column
                  dataField="cierre.fechaCierre"
                  caption={t('presupuestos.cierreDetalle.fecha')}
                  dataType="date"
                  format="dd/MM/yyyy"
                />
              </>
            ) : null
          }
        />
      </DataGridDx>
      {saveAsDialog}
      <PresupuestoCierreDialog
        visible={cierreDialogVisible}
        presupuesto={cierreTarget}
        onClose={() => {
          setCierreDialogVisible(false);
          setCierreTarget(null);
        }}
        onClosed={reloadGrid}
      />
      <PresupuestoCierreDetalleDialog
        visible={cierreDetalleVisible}
        presupuesto={cierreDetalleTarget}
        onClose={() => {
          setCierreDetalleVisible(false);
          setCierreDetalleTarget(null);
        }}
      />
    </section>
  );
}
