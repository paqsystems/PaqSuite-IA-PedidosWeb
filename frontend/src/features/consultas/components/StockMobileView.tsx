import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
import {
  formatConsultaAmount,
} from '../../../shared/consultas/consultaMobileUtils';
import { fetchStockPage, type StockConsultaRow } from '../api/consultaApi';

export function StockMobileView() {
  const { t } = useTranslation();

  const fetchPage = useCallback(
    (params: { page: number; pageSize: number; q?: string }) => fetchStockPage(params),
    [],
  );

  return (
    <ConsultaKardexMobileView
      mode="server"
      pageTestId="page-consulta-stock-mobile"
      pageTitleKey="pages.consultaStock"
      listTestId="stockKardexList"
      filterTestId="stockFilterQ"
      resultSummaryTestId="stockResultSummary"
      errorTestId="stockMobileError"
      detailPopupTestId="stockDetailPopup"
      keyExpr="id"
      fetchPage={fetchPage}
      detailTitle={(item) => item.codArticulo}
      detailFields={[
        { labelKey: 'consultas.column.descripcion', getValue: (item) => item.descripcion },
        { labelKey: 'consultas.column.stock', getValue: (item) => formatConsultaAmount(item.stock) },
        {
          labelKey: 'consultas.column.comprometido',
          getValue: (item) => formatConsultaAmount(item.comprometido),
        },
        {
          labelKey: 'consultas.column.comprometidoWeb',
          getValue: (item) => formatConsultaAmount(item.comprometidoWeb),
        },
        {
          labelKey: 'consultas.column.disponibleNeto',
          getValue: (item) => formatConsultaAmount(item.disponibleNeto),
        },
        {
          labelKey: 'consultas.column.codBase',
          getValue: (item) => item.codBase ?? '—',
          visible: (item) => Boolean(item.codBase),
        },
        {
          labelKey: 'consultas.column.stockBase',
          getValue: (item) => formatConsultaAmount(item.stockBase),
          visible: (item) => Boolean(item.codBase),
        },
        {
          labelKey: 'consultas.column.disponibleNetoBase',
          getValue: (item) => formatConsultaAmount(item.disponibleNetoBase),
          visible: (item) => Boolean(item.codBase),
        },
      ]}
      renderCard={(item: StockConsultaRow) => (
        <article className="consultaKardexCard">
          <div className="consultaKardexCard__title">{item.codArticulo}</div>
          <div className="consultaKardexCard__subtitle">{item.descripcion}</div>
          <div className="consultaKardexCard__metrics">
            <span>
              {t('consultas.column.disponibleNeto')}: {formatConsultaAmount(item.disponibleNeto)}
            </span>
            <span>
              {t('consultas.column.stock')}: {formatConsultaAmount(item.stock)}
            </span>
          </div>
        </article>
      )}
    />
  );
}
