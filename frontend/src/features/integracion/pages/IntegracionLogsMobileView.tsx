import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import DateBox from 'devextreme-react/date-box';
import SelectBox from 'devextreme-react/select-box';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
import { formatConsultaDate } from '../../../shared/consultas/consultaMobileUtils';
import { fetchIntegracionLogs, type IntegracionLogRow } from '../api/integracionApi';
import '../../../shared/consultas/consultaKardexList.css';

const severidadOptions = ['info', 'warning', 'error'] as const;

type IntegracionFilters = {
  fechaDesde: Date | null;
  fechaHasta: Date | null;
  severidad: string | null;
};

export function IntegracionLogsMobileView() {
  const { t } = useTranslation();
  const [draftFilters, setDraftFilters] = useState<IntegracionFilters>({
    fechaDesde: null,
    fechaHasta: null,
    severidad: null,
  });
  const [appliedFilters, setAppliedFilters] = useState<IntegracionFilters>({
    fechaDesde: null,
    fechaHasta: null,
    severidad: null,
  });

  const severidadDataSource = useMemo(
    () =>
      severidadOptions.map((value) => ({
        value,
        label: t(`integracion.severidad.${value}`),
      })),
    [t],
  );

  const loadData = useCallback(async () => {
    const result = await fetchIntegracionLogs({
      fechaDesde: appliedFilters.fechaDesde ? appliedFilters.fechaDesde.toISOString() : null,
      fechaHasta: appliedFilters.fechaHasta ? appliedFilters.fechaHasta.toISOString() : null,
      severidad: appliedFilters.severidad,
    });

    return {
      items: result.items,
      meta: result.fechaProceso ? { fecha_proceso: result.fechaProceso } : null,
    };
  }, [appliedFilters]);

  return (
    <section data-testid="page-integracion-logs-mobile">
      <div className="consultaMobilePage__filterRow integracionLogsMobile__filters">
        <DateBox
          value={draftFilters.fechaDesde}
          onValueChanged={(event) => {
            setDraftFilters((current) => ({
              ...current,
              fechaDesde: (event.value as Date | null) ?? null,
            }));
          }}
          placeholder={t('integracion.filterDesde')}
          inputAttr={{ 'data-testid': 'logsIntegracionFilterDesde' }}
        />
        <DateBox
          value={draftFilters.fechaHasta}
          onValueChanged={(event) => {
            setDraftFilters((current) => ({
              ...current,
              fechaHasta: (event.value as Date | null) ?? null,
            }));
          }}
          placeholder={t('integracion.filterHasta')}
          inputAttr={{ 'data-testid': 'logsIntegracionFilterHasta' }}
        />
        <SelectBox
          dataSource={severidadDataSource}
          valueExpr="value"
          displayExpr="label"
          value={draftFilters.severidad}
          showClearButton={true}
          onValueChanged={(event) => {
            setDraftFilters((current) => ({
              ...current,
              severidad: (event.value as string | null) ?? null,
            }));
          }}
          placeholder={t('integracion.filterSeveridad')}
          inputAttr={{ 'data-testid': 'logsIntegracionFilterSeveridad' }}
        />
        <Button
          text={t('integracion.aplicarFiltros')}
          stylingMode="contained"
          onClick={() => {
            setAppliedFilters({ ...draftFilters });
          }}
        />
      </div>

      <ConsultaKardexMobileView
        mode="client"
        pageTestId="page-integracion-logs-kardex"
        pageTitleKey="pages.integracionLogs"
        listTestId="integracionLogsKardexList"
        keyExpr="id"
        loadData={loadData}
        metaLabelKey="integracion.fechaProceso"
        detailTitle={(item) => item.tipo}
        detailFields={[
          { labelKey: 'integracion.column.fecha', getValue: (item) => formatConsultaDate(item.fecha) },
          { labelKey: 'integracion.column.tipo', getValue: (item) => item.tipo },
          { labelKey: 'integracion.column.severidad', getValue: (item) => item.severidad },
          { labelKey: 'integracion.column.origen', getValue: (item) => item.origen },
          { labelKey: 'integracion.column.mensaje', getValue: (item) => item.mensaje },
          {
            labelKey: 'integracion.column.procesado',
            getValue: (item) =>
              item.procesado ? t('gridExport.boolean.true') : t('gridExport.boolean.false'),
          },
        ]}
        renderCard={(item: IntegracionLogRow) => (
          <article className="consultaKardexCard">
            <div className="consultaKardexCard__title">{item.tipo}</div>
            <div className="consultaKardexCard__subtitle">{item.mensaje}</div>
            <div className="consultaKardexCard__metrics">
              <span>{item.severidad}</span>
              <span>{formatConsultaDate(item.fecha)}</span>
            </div>
          </article>
        )}
      />
    </section>
  );
}
