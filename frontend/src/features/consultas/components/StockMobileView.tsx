import { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../../../shared/http/client';
import { ConsultaKardexList } from '../../../shared/consultas/ConsultaKardexList';
import '../../../shared/consultas/consultaKardexList.css';
import { fetchStockPage, type StockConsultaRow } from '../api/consultaApi';
import { StockDetailPopup } from './StockDetailPopup';
import './stockDetailPopup.css';

const defaultPageSize = 20;

function formatAmount(value: number): string {
  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

export function StockMobileView() {
  const { t } = useTranslation();
  const [items, setItems] = useState<StockConsultaRow[]>([]);
  const [selectedItem, setSelectedItem] = useState<StockConsultaRow | null>(null);
  const [filterQuery, setFilterQuery] = useState('');
  const [appliedQuery, setAppliedQuery] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [fechaProceso, setFechaProceso] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [errorKey, setErrorKey] = useState<string | null>(null);

  const loadPage = useCallback(
    async (nextPage: number, query: string, append: boolean) => {
      setIsLoading(true);
      setErrorKey(null);

      try {
        const result = await fetchStockPage({
          page: nextPage,
          pageSize: defaultPageSize,
          q: query.trim().length > 0 ? query.trim() : undefined,
        });

        setItems((currentItems) => (append ? [...currentItems, ...result.items] : result.items));
        setPage(result.page);
        setTotalPages(result.totalPages);
        setTotalItems(result.total);
        setFechaProceso(result.meta?.fecha_proceso ?? null);
      } catch (error) {
        setItems([]);
        if (error instanceof ApiClientError && error.status === 403) {
          setErrorKey('auth.noPermission');
        } else {
          setErrorKey('consultas.loadFailed');
        }
      } finally {
        setIsLoading(false);
      }
    },
    [],
  );

  useEffect(() => {
    void loadPage(1, appliedQuery, false);
  }, [appliedQuery, loadPage]);

  function handleRefresh() {
    void loadPage(1, appliedQuery, false);
  }

  function handleLoadMore() {
    if (page >= totalPages || isLoading) {
      return;
    }

    void loadPage(page + 1, appliedQuery, true);
  }

  return (
    <section className="stockMobilePage" data-testid="page-consulta-stock-mobile">
      <header className="stockMobilePage__header">
        <h1>{t('pages.consultaStock')}</h1>
        {fechaProceso && (
          <p className="stockMobilePage__meta">
            {t('consultas.fechaProceso', { value: fechaProceso })}
          </p>
        )}
      </header>

      <div className="stockMobilePage__filterRow">
        <label className="stockMobilePage__filter">
          <span>{t('consultas.filterSearch')}</span>
          <TextBox
            value={filterQuery}
            width="100%"
            stylingMode="outlined"
            valueChangeEvent="input"
            inputAttr={{ 'data-testid': 'stockFilterQ' }}
            onValueChanged={(event) => {
              setFilterQuery(String(event.value ?? ''));
            }}
            onEnterKey={() => {
              setAppliedQuery(filterQuery);
            }}
          />
        </label>
        <Button
          className="stockMobilePage__refresh"
          icon="refresh"
          stylingMode="outlined"
          hint={t('grid.refresh')}
          disabled={isLoading}
          elementAttr={{ 'data-testid': 'gridRefresh' }}
          onClick={handleRefresh}
        />
      </div>

      {!isLoading && items.length > 0 && (
        <p className="stockMobilePage__resultSummary" data-testid="stockResultSummary">
          {t('consultas.resultSummary', { shown: items.length, total: totalItems })}
        </p>
      )}

      {errorKey && (
        <p className="stockMobilePage__error" data-testid="stockMobileError">
          {t(errorKey)}
        </p>
      )}

      <ConsultaKardexList
        items={items}
        keyExpr="id"
        isLoading={isLoading}
        hasMore={page < totalPages}
        refreshLabel={t('grid.refresh')}
        loadMoreLabel={t('consultas.loadMore')}
        listTestId="stockKardexList"
        emptyMessage={t('consultas.empty')}
        hideToolbar
        onRefresh={handleRefresh}
        onLoadMore={handleLoadMore}
        onItemClick={(item) => {
          setSelectedItem(item);
        }}
        renderCard={(item) => (
          <article className="consultaKardexCard">
            <div className="consultaKardexCard__title">{item.codArticulo}</div>
            <div className="consultaKardexCard__subtitle">{item.descripcion}</div>
            <div className="consultaKardexCard__metrics">
              <span>
                {t('consultas.column.disponibleNeto')}: {formatAmount(item.disponibleNeto)}
              </span>
              <span>
                {t('consultas.column.stock')}: {formatAmount(item.stock)}
              </span>
            </div>
          </article>
        )}
      />

      <StockDetailPopup
        item={selectedItem}
        onClose={() => {
          setSelectedItem(null);
        }}
      />
    </section>
  );
}
