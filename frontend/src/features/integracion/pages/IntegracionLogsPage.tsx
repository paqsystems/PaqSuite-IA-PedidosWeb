import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import DateBox from 'devextreme-react/date-box';
import SelectBox from 'devextreme-react/select-box';
import { Column } from 'devextreme-react/data-grid';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { DataGridDx } from '../../../shared/ui/grids';
import { fetchIntegracionLogs, type IntegracionLogRow } from '../api/integracionApi';
import { IntegracionLogsMobileView } from './IntegracionLogsMobileView';

const proceso = 'pw_logsintegracion';
const gridId = 'pw_logsintegracion';

const severidadOptions = ['info', 'warning', 'error'] as const;

export function IntegracionLogsPage() {
  if (isNativeApp()) {
    return <IntegracionLogsMobileView />;
  }

  return <IntegracionLogsWebView />;
}

function IntegracionLogsWebView() {
  const { t } = useTranslation();
  const [fechaDesde, setFechaDesde] = useState<Date | null>(null);
  const [fechaHasta, setFechaHasta] = useState<Date | null>(null);
  const [severidad, setSeveridad] = useState<string | null>(null);
  const [rows, setRows] = useState<IntegracionLogRow[]>([]);
  const [fechaProceso, setFechaProceso] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);

  const severidadDataSource = useMemo(
    () =>
      severidadOptions.map((value) => ({
        value,
        label: t(`integracion.severidad.${value}`),
      })),
    [t],
  );

  const loadLogs = useCallback(async () => {
    setIsLoading(true);
    setLoadError(null);
    try {
      const result = await fetchIntegracionLogs({
        fechaDesde: fechaDesde ? fechaDesde.toISOString() : null,
        fechaHasta: fechaHasta ? fechaHasta.toISOString() : null,
        severidad,
      });
      setRows(result.items);
      setFechaProceso(result.fechaProceso);
    } catch {
      setRows([]);
      setFechaProceso(null);
      setLoadError(t('grid.error.load'));
    } finally {
      setIsLoading(false);
    }
  }, [fechaDesde, fechaHasta, severidad, t]);

  useEffect(() => {
    void loadLogs();
  }, [loadLogs]);

  return (
    <section data-testid="page-integracion-logs">
      <h2>{t('pages.integracionLogs')}</h2>
      <p>
        {t('integracion.fechaProceso', {
          value: fechaProceso ?? t('integracion.fechaProcesoSinDato'),
        })}
      </p>

      <div className="integracionLogsPage__filters">
        <DateBox
          value={fechaDesde}
          onValueChanged={(event) => {
            setFechaDesde((event.value as Date | null) ?? null);
          }}
          placeholder={t('integracion.filterDesde')}
          inputAttr={{ 'data-testid': 'logsIntegracionFilterDesde' }}
        />
        <DateBox
          value={fechaHasta}
          onValueChanged={(event) => {
            setFechaHasta((event.value as Date | null) ?? null);
          }}
          placeholder={t('integracion.filterHasta')}
          inputAttr={{ 'data-testid': 'logsIntegracionFilterHasta' }}
        />
        <SelectBox
          dataSource={severidadDataSource}
          valueExpr="value"
          displayExpr="label"
          value={severidad}
          showClearButton={true}
          onValueChanged={(event) => {
            setSeveridad((event.value as string | null) ?? null);
          }}
          placeholder={t('integracion.filterSeveridad')}
          inputAttr={{ 'data-testid': 'logsIntegracionFilterSeveridad' }}
        />
        <Button
          text={t('integracion.aplicarFiltros')}
          stylingMode="contained"
          onClick={() => {
            void loadLogs();
          }}
        />
      </div>

      <div data-testid="logsIntegracionGrid">
        <DataGridDx<IntegracionLogRow>
          proceso={proceso}
          gridId={gridId}
          dataSource={rows}
          keyExpr="id"
          isLoading={isLoading}
          loadError={loadError}
          rowActions={[]}
          exportEnabled={true}
        >
          <Column dataField="fecha" caption={t('integracion.column.fecha')} />
          <Column dataField="tipo" caption={t('integracion.column.tipo')} />
          <Column dataField="severidad" caption={t('integracion.column.severidad')} />
          <Column dataField="origen" caption={t('integracion.column.origen')} />
          <Column dataField="mensaje" caption={t('integracion.column.mensaje')} />
          <Column
            dataField="procesado"
            caption={t('integracion.column.procesado')}
            dataType="boolean"
          />
        </DataGridDx>
      </div>
    </section>
  );
}
