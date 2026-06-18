import { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import DateBox from 'devextreme-react/date-box';
import { DataGridDx, type DataGridRowAction } from '../../../shared/ui/grids';
import { GridRefreshButton } from '../../consultas/components/GridRefreshButton';
import { fetchExcelImportHistorial, type ExcelHistorialRow } from '../api/excelImportApi';
import './excelImportPages.css';

const proceso = 'pw_historialimportexcel';
const gridId = 'excelHistoryGrid';

const estados = [
  'pendiente',
  'validando',
  'validada',
  'con_error_estructura',
  'lista_para_procesar',
  'procesando',
  'procesada',
  'procesada_parcial',
  'cancelada',
] as const;

export function ExcelImportHistoryPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [rows, setRows] = useState<ExcelHistorialRow[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [refreshToken, setRefreshToken] = useState(0);
  const [estadoFiltro, setEstadoFiltro] = useState<string | null>(null);
  const [fechaDesde, setFechaDesde] = useState<Date | null>(null);
  const [fechaHasta, setFechaHasta] = useState<Date | null>(null);

  const load = useCallback(async () => {
    setIsLoading(true);
    setLoadError(null);
    try {
      const result = await fetchExcelImportHistorial({
        page: 1,
        pageSize: 50,
        estadoImportacion: estadoFiltro ?? undefined,
        fechaDesde: fechaDesde ? fechaDesde.toISOString().slice(0, 10) : undefined,
        fechaHasta: fechaHasta ? fechaHasta.toISOString().slice(0, 10) : undefined,
      });
      setRows(result.items);
    } catch {
      setLoadError(t('grid.error.load'));
      setRows([]);
    } finally {
      setIsLoading(false);
    }
  }, [estadoFiltro, fechaDesde, fechaHasta, t]);

  useEffect(() => {
    void load();
  }, [load, refreshToken]);

  const rowActions = useMemo<DataGridRowAction<ExcelHistorialRow>[]>(
    () => [
      {
        actionKey: 'detail',
        icon: 'find',
        hintKey: 'excelImport.historyDetail',
        onClick: (row) => navigate(`/excel-import/lotes/${row.guidImportacion}?readOnly=1`),
      },
    ],
    [navigate],
  );

  const estadoItems = useMemo(
    () =>
      estados.map((estado) => ({
        value: estado,
        label: t(`excelImport.status.${estado}`),
      })),
    [t],
  );

  const toolbarStart = useMemo(
    () => (
      <div className="excelImportHistory__filters">
        <SelectBox
          items={estadoItems}
          valueExpr="value"
          displayExpr="label"
          value={estadoFiltro}
          showClearButton={true}
          placeholder={t('excelImport.filterEstado')}
          onValueChanged={(event) => setEstadoFiltro((event.value as string | null) ?? null)}
        />
        <DateBox
          value={fechaDesde}
          type="date"
          displayFormat="dd/MM/yyyy"
          placeholder={t('excelImport.filterFechaDesde')}
          onValueChanged={(event) => setFechaDesde((event.value as Date | null) ?? null)}
        />
        <DateBox
          value={fechaHasta}
          type="date"
          displayFormat="dd/MM/yyyy"
          placeholder={t('excelImport.filterFechaHasta')}
          onValueChanged={(event) => setFechaHasta((event.value as Date | null) ?? null)}
        />
        <Button text={t('integracion.aplicarFiltros')} stylingMode="outlined" onClick={() => setRefreshToken((v) => v + 1)} />
      </div>
    ),
    [estadoFiltro, estadoItems, fechaDesde, fechaHasta, t],
  );

  const toolbarEnd = useMemo(
    () => <GridRefreshButton onRefresh={() => setRefreshToken((value) => value + 1)} />,
    [],
  );

  return (
    <main className="excelImportPage">
      <header className="excelImportPage__header">
        <h1>{t('excelImport.historyTitle')}</h1>
      </header>

      <DataGridDx
        proceso={proceso}
        gridId={gridId}
        dataSource={rows}
        keyExpr="guidImportacion"
        isLoading={isLoading}
        loadError={loadError}
        rowActions={rowActions}
        toolbarStart={toolbarStart}
        toolbarEnd={toolbarEnd}
        exportEnabled={false}
        defaultPageSize={50}
      >
        <Column dataField="fechaInicio" caption={t('excelImport.column.fechaInicio')} dataType="datetime" format="dd/MM/yyyy HH:mm" />
        <Column dataField="usuarioEjecucion" caption={t('excelImport.column.usuario')} />
        <Column dataField="codigoProceso" caption={t('excelImport.column.proceso')} />
        <Column dataField="archivoOriginalNombre" caption={t('excelImport.column.archivo')} />
        <Column dataField="hojaSeleccionada" caption={t('excelImport.column.hoja')} />
        <Column
          dataField="estadoImportacion"
          caption={t('excelImport.column.estado')}
          calculateCellValue={(row: ExcelHistorialRow) => t(`excelImport.status.${row.estadoImportacion}`)}
        />
        <Column dataField="cantidadFilasLeidas" caption={t('excelImport.column.filasLeidas')} dataType="number" />
        <Column dataField="cantidadFilasValidas" caption={t('excelImport.column.filasValidas')} dataType="number" />
        <Column dataField="cantidadFilasConError" caption={t('excelImport.column.filasError')} dataType="number" />
        <Column dataField="cantidadFilasProcesadas" caption={t('excelImport.column.filasProcesadas')} dataType="number" />
      </DataGridDx>
    </main>
  );
}
