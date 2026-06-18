import { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import { Column } from 'devextreme-react/data-grid';
import type { RowPreparedEvent } from 'devextreme/ui/data_grid';
import { DataGridDx } from '../../../shared/ui/grids';
import { GridRefreshButton } from '../../consultas/components/GridRefreshButton';
import {
  cancelExcelImportLot,
  fetchExcelImportLot,
  fetchExcelStagingColumnas,
  fetchExcelStagingFilas,
  flattenStagingRow,
  processExcelImportLot,
  type ExcelStagingColumn,
  type ExcelStagingColumnasMeta,
} from '../api/excelImportApi';
import './excelImportPages.css';

const proceso = 'pw_excelimport';
const gridId = 'excel_staging';
const defaultPageSize = 50;

function buildColumns(columnas: ExcelStagingColumn[], t: (key: string) => string) {
  return columnas.map((columna) => {
    const props: Record<string, unknown> = {
      dataField: columna.dataField,
      caption:
        columna.dataField === 'errorImportacion'
          ? t('excelImport.column.errores')
          : columna.caption,
    };

    if (columna.tipoDato === 'decimal' || columna.tipoDato === 'entero') {
      props.dataType = 'number';
      props.format = columna.format ?? '#,##0.00';
    }

    if (columna.tipoDato === 'fecha') {
      props.dataType = 'date';
      props.format = 'dd/MM/yyyy';
    }

    return <Column key={columna.dataField} {...props} />;
  });
}

export function ExcelStagingGridPage() {
  const { guidImportacion = '' } = useParams<{ guidImportacion: string }>();
  const [searchParams] = useSearchParams();
  const readOnly = searchParams.get('readOnly') === '1';
  const { t } = useTranslation();
  const navigate = useNavigate();

  const [rows, setRows] = useState<Record<string, unknown>[]>([]);
  const [columnasMeta, setColumnasMeta] = useState<ExcelStagingColumnasMeta | null>(null);
  const [lotSummary, setLotSummary] = useState<Awaited<ReturnType<typeof fetchExcelImportLot>> | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [refreshToken, setRefreshToken] = useState(0);
  const [confirmVisible, setConfirmVisible] = useState(false);
  const [isProcessing, setIsProcessing] = useState(false);

  const reload = useCallback(async () => {
    if (!guidImportacion) {
      return;
    }

    setIsLoading(true);
    setLoadError(null);
    try {
      const [meta, filas, lot] = await Promise.all([
        fetchExcelStagingColumnas(guidImportacion),
        fetchExcelStagingFilas(guidImportacion, 1, defaultPageSize),
        fetchExcelImportLot(guidImportacion),
      ]);
      setColumnasMeta(meta);
      setLotSummary(lot);
      setRows(filas.items.map(flattenStagingRow));
    } catch {
      setLoadError(t('grid.error.load'));
      setRows([]);
    } finally {
      setIsLoading(false);
    }
  }, [guidImportacion, t]);

  useEffect(() => {
    void reload();
  }, [reload, refreshToken]);

  const puedeProcesar = columnasMeta?.puedeProcesar ?? false;
  const permiteSoloValidar = columnasMeta?.permiteSoloValidar ?? false;

  const toolbarEnd = useMemo(
    () => (
      <>
        <GridRefreshButton onRefresh={() => setRefreshToken((value) => value + 1)} />
        {!readOnly && !permiteSoloValidar ? (
          <div data-testid="excelProcessConfirm">
            <Button
              text={t('excelImport.process')}
              type="default"
              disabled={!puedeProcesar || isProcessing}
              onClick={() => setConfirmVisible(true)}
            />
          </div>
        ) : null}
        {!readOnly ? (
          <div data-testid="excelImportCancel">
            <Button
              text={t('excelImport.cancel')}
              stylingMode="outlined"
              disabled={!lotSummary?.puedeCancelar}
              onClick={() => void cancelExcelImportLot(guidImportacion).then(() => navigate('/excel-import/historial'))}
            />
          </div>
        ) : null}
      </>
    ),
    [
      guidImportacion,
      isProcessing,
      lotSummary?.puedeCancelar,
      navigate,
      permiteSoloValidar,
      puedeProcesar,
      readOnly,
      t,
    ],
  );

  const handleRowPrepared = useCallback((event: RowPreparedEvent) => {
    const data = event.data as { tieneError?: boolean } | undefined;
    if (data?.tieneError) {
      event.rowElement.classList.add('excel-import-row-error');
    }
  }, []);

  const handleConfirmProcess = useCallback(async () => {
    setIsProcessing(true);
    try {
      await processExcelImportLot(guidImportacion);
      setConfirmVisible(false);
      setRefreshToken((value) => value + 1);
    } finally {
      setIsProcessing(false);
    }
  }, [guidImportacion]);

  const columnNodes = useMemo(
    () => buildColumns(columnasMeta?.columnas ?? [], t),
    [columnasMeta?.columnas, t],
  );

  const partialSummary = useMemo(() => {
    if (!columnasMeta) {
      return '';
    }

    return t('excelImport.processPartialSummary', {
      validas: columnasMeta.cantidadFilasValidas,
      errores: columnasMeta.cantidadFilasConError,
    });
  }, [columnasMeta, t]);

  return (
    <main className="excelImportPage">
      <header className="excelImportPage__header">
        <h1>{lotSummary?.nombreProceso ?? t('excelImport.stagingTitle')}</h1>
        {lotSummary ? (
          <p className="excelImportPage__subtitle">
            {lotSummary.archivoOriginalNombre} — {lotSummary.hojaSeleccionada}
          </p>
        ) : null}
      </header>

      <DataGridDx
        proceso={proceso}
        gridId={gridId}
        dataSource={rows}
        keyExpr="idImportacionFila"
        isLoading={isLoading}
        loadError={loadError}
        defaultPageSize={defaultPageSize}
        exportEnabled={false}
        toolbarEnd={toolbarEnd}
        onRowPrepared={handleRowPrepared}
      >
        {columnNodes}
      </DataGridDx>

      <Popup
        visible={confirmVisible}
        onHiding={() => setConfirmVisible(false)}
        title={t('excelImport.processConfirmTitle')}
        width={420}
        height="auto"
        showCloseButton={true}
      >
        <p>{partialSummary}</p>
        <div className="excelImportPage__confirmActions">
          <Button text={t('excelImport.process')} type="default" onClick={() => void handleConfirmProcess()} />
          <Button text={t('excelImport.cancel')} stylingMode="outlined" onClick={() => setConfirmVisible(false)} />
        </div>
      </Popup>
    </main>
  );
}
