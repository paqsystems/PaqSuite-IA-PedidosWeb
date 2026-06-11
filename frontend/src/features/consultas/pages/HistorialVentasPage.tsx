import { useCallback, useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { ConsultaGrillaPivotShell } from '../../../shared/pivot';
import { useTranslation } from 'react-i18next';
import Popup from 'devextreme-react/popup';
import { Column } from 'devextreme-react/data-grid';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { DataGridDx, type DataGridDxHandle, type DataGridRowAction } from '../../../shared/ui/grids';
import { GridRefreshButton } from '../components/GridRefreshButton';
import {
  fetchHistorialVentas,
  toHistorialDetalleRows,
  type ConsultaMeta,
  type HistorialVentasRow,
} from '../api/consultaApi';
import { formatConsultaFechaProceso } from '../utils/formatConsultaFechaProceso';

const proceso = 'pw_historialventas';
const gridId = 'pw_historialventas';
const pivotConsultaId = 'CONSULTA_PILOTO_PIVOT';

const dateColumnProps = {
  dataType: 'date' as const,
  format: 'dd/MM/yyyy',
};

const decimalColumnProps = {
  dataType: 'number' as const,
  format: '#,##0.00',
};

function historialColumns(t: (key: string) => string) {
  return (
    <>
      <Column dataField="codCliente" caption={t('consultas.column.cliente')} />
      <Column dataField="razonSocial" caption={t('consultas.column.razonSocial')} />
      <Column dataField="nRemito" caption={t('consultas.column.nRemito')} />
      <Column dataField="tipo" caption={t('consultas.column.tipo')} />
      <Column dataField="numero" caption={t('consultas.column.numero')} />
      <Column dataField="fechaEmision" caption={t('consultas.column.fechaEmision')} {...dateColumnProps} />
      <Column dataField="condVta" caption={t('consultas.column.condVta')} dataType="number" />
      <Column dataField="porcDesc" caption={t('consultas.column.porcDesc')} {...decimalColumnProps} />
      <Column dataField="cotiz" caption={t('consultas.column.cotiz')} {...decimalColumnProps} />
      <Column dataField="moneda" caption={t('consultas.column.moneda')} />
      <Column dataField="totalComp" caption={t('consultas.column.totalComp')} {...decimalColumnProps} />
      <Column dataField="codTransp" caption={t('consultas.column.codTransp')} />
      <Column dataField="nomTransp" caption={t('consultas.column.nomTransp')} />
      <Column dataField="codArticulo" caption={t('consultas.column.codArticulo')} />
      <Column dataField="descripcion" caption={t('consultas.column.descripcion')} />
      <Column dataField="codDep" caption={t('consultas.column.codDep')} />
      <Column dataField="um" caption={t('consultas.column.um')} />
      <Column dataField="cantidad" caption={t('consultas.column.cantidad')} {...decimalColumnProps} />
      <Column dataField="precio" caption={t('consultas.column.precio')} {...decimalColumnProps} />
      <Column dataField="totSinImp" caption={t('consultas.column.totSinImp')} {...decimalColumnProps} />
      <Column dataField="nCompRem" caption={t('consultas.column.nCompRem')} />
      <Column dataField="cantRem" caption={t('consultas.column.cantRem')} {...decimalColumnProps} />
      <Column dataField="fechaRem" caption={t('consultas.column.fechaRem')} {...dateColumnProps} />
    </>
  );
}

export function HistorialVentasPage() {
  const { t, i18n } = useTranslation();
  const gridRef = useRef<DataGridDxHandle>(null);
  const { toolbar: layoutToolbar, saveAsDialog } = useGridLayouts({
    proceso,
    gridId,
    gridRef,
  });
  const [rows, setRows] = useState<HistorialVentasRow[]>([]);
  const [meta, setMeta] = useState<ConsultaMeta | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [detalleVisible, setDetalleVisible] = useState(false);
  const [detalleRows, setDetalleRows] = useState<HistorialVentasRow[]>([]);
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
        const result = await fetchHistorialVentas();
        if (mounted) {
          setRows(result.items);
          setMeta(result.meta);
        }
      } catch {
        if (mounted) {
          setRows([]);
          setMeta(null);
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
  }, [refreshToken, t]);

  const handleOpenDetalle = useCallback((row: HistorialVentasRow) => {
    setDetalleRows(toHistorialDetalleRows(row));
    setDetalleVisible(true);
  }, []);

  const visibleRows = useMemo(() => {
    if (!drillDownFilters) {
      return rows;
    }

    return rows.filter((row) =>
      Object.entries(drillDownFilters).every(([field, value]) => {
        const rowValue = row[field as keyof HistorialVentasRow];
        return String(rowValue ?? '') === String(value ?? '');
      }),
    );
  }, [drillDownFilters, rows]);

  const rowActions: DataGridRowAction<HistorialVentasRow>[] = [
    {
      actionKey: 'verDetalle',
      icon: 'info',
      hintKey: 'grid.action.viewDetail',
      onClick: (row) => {
        handleOpenDetalle(row);
      },
    },
  ];

  return (
    <section data-testid="page-consulta-historial">
      <h2>{t('pages.consultaHistorial')}</h2>
      <p>{t('consultas.fechaProceso', { value: fechaProcesoLabel })}</p>
      {meta?.dias_ventas_detalladas ? (
        <p>{t('consultas.historialPeriodo', { dias: meta.dias_ventas_detalladas })}</p>
      ) : null}
      <ConsultaGrillaPivotShell
        consultaId={pivotConsultaId}
        tipoProceso="informe"
        testIdPrefix="historialVentas"
        refreshToken={refreshToken}
        onRefresh={() => setRefreshToken((value) => value + 1)}
        onDrillDownFilters={setDrillDownFilters}
        gridContent={
          (
            <DataGridDx<HistorialVentasRow>
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
              {historialColumns(t)}
            </DataGridDx>
          ) as ReactNode
        }
      />
      {saveAsDialog}
      <Popup
        visible={detalleVisible}
        onHiding={() => setDetalleVisible(false)}
        dragEnabled={false}
        showCloseButton={true}
        width="90%"
        height={480}
        title={t('consultas.historialDetalleTitle')}
        elementAttr={{ 'data-testid': 'consultaHistorialDetallePopup' }}
      >
        <DataGridDx<HistorialVentasRow>
          proceso={proceso}
          gridId="pw_historialventas_detalle"
          dataSource={detalleRows}
          keyExpr="id"
          exportEnabled={false}
          enableGrouping={false}
        >
          {historialColumns(t)}
        </DataGridDx>
      </Popup>
    </section>
  );
}
