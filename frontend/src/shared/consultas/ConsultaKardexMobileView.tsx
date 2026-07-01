import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../http/client';
import { ConsultaKardexList } from './ConsultaKardexList';
import { ConsultaDetailPopup, type ConsultaDetailField } from './ConsultaDetailPopup';
import { matchesConsultaQuery } from './consultaMobileUtils';
import type { ConsultaMeta, ConsultaResult } from '../../features/consultas/api/consultaApi';
import './consultaKardexList.css';

const defaultPageSize = 20;

type PagedConsultaResult<TItem> = ConsultaResult<TItem> & {
  page: number;
  pageSize: number;
  total: number;
  totalPages: number;
};

type ConsultaKardexMobileViewBaseProps<TItem> = {
  pageTestId: string;
  pageTitleKey: string;
  listTestId: string;
  filterTestId?: string;
  resultSummaryTestId?: string;
  errorTestId?: string;
  detailPopupTestId?: string;
  keyExpr: keyof TItem | ((item: TItem) => string);
  renderCard: (item: TItem) => ReactNode;
  detailTitle: (item: TItem) => string;
  detailFields: ConsultaDetailField<TItem>[];
  metaLabelKey?: string;
  renderDetailFooter?: (item: TItem, onClose: () => void) => ReactNode;
  renderCardActions?: (item: TItem) => ReactNode;
  refreshToken?: number;
};

type ServerModeProps<TItem> = ConsultaKardexMobileViewBaseProps<TItem> & {
  mode: 'server';
  fetchPage: (params: {
    page: number;
    pageSize: number;
    q?: string;
  }) => Promise<PagedConsultaResult<TItem>>;
};

type ClientModeProps<TItem> = ConsultaKardexMobileViewBaseProps<TItem> & {
  mode: 'client';
  loadData: () => Promise<ConsultaResult<TItem>>;
  filterItem?: (item: TItem, query: string) => boolean;
};

export type ConsultaKardexMobileViewProps<TItem> = ServerModeProps<TItem> | ClientModeProps<TItem>;

export function ConsultaKardexMobileView<TItem>(
  props: ConsultaKardexMobileViewProps<TItem>,
) {
  const { t } = useTranslation();
  const [items, setItems] = useState<TItem[]>([]);
  const [selectedItem, setSelectedItem] = useState<TItem | null>(null);
  const [filterQuery, setFilterQuery] = useState('');
  const [appliedQuery, setAppliedQuery] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [fechaProceso, setFechaProceso] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [errorKey, setErrorKey] = useState<string | null>(null);
  const [allItems, setAllItems] = useState<TItem[]>([]);
  const [clientMeta, setClientMeta] = useState<ConsultaMeta | null>(null);

  const filterItem = useMemo(() => {
    if (props.mode === 'client' && props.filterItem) {
      return props.filterItem;
    }

    return (item: TItem, query: string) => matchesConsultaQuery(item as Record<string, unknown>, query);
  }, [props]);

  const loadServerPage = useCallback(
    async (nextPage: number, query: string, append: boolean) => {
      if (props.mode !== 'server') {
        return;
      }

      setIsLoading(true);
      setErrorKey(null);

      try {
        const result = await props.fetchPage({
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
    [props],
  );

  const loadClientData = useCallback(async () => {
    if (props.mode !== 'client') {
      return;
    }

    setIsLoading(true);
    setErrorKey(null);

    try {
      const result = await props.loadData();
      setAllItems(result.items);
      setClientMeta(result.meta);
      setFechaProceso(result.meta?.fecha_proceso ?? null);
    } catch (error) {
      setAllItems([]);
      if (error instanceof ApiClientError && error.status === 403) {
        setErrorKey('auth.noPermission');
      } else {
        setErrorKey('consultas.loadFailed');
      }
    } finally {
      setIsLoading(false);
    }
  }, [props]);

  useEffect(() => {
    if (props.mode === 'server') {
      void loadServerPage(1, appliedQuery, false);
      return;
    }

    void loadClientData();
  }, [appliedQuery, loadClientData, loadServerPage, props.mode, props.refreshToken]);

  useEffect(() => {
    if (props.mode !== 'client') {
      return;
    }

    const filteredItems = allItems.filter((item) => filterItem(item, appliedQuery));
    const visibleCount = Math.min(page * defaultPageSize, filteredItems.length);

    setItems(filteredItems.slice(0, visibleCount));
    setTotalItems(filteredItems.length);
    setTotalPages(Math.max(1, Math.ceil(filteredItems.length / defaultPageSize)));
  }, [allItems, appliedQuery, filterItem, page, props.mode]);

  useEffect(() => {
    if (props.mode === 'client') {
      setPage(1);
    }
  }, [appliedQuery, props.mode]);

  function handleRefresh() {
    if (props.mode === 'server') {
      void loadServerPage(1, appliedQuery, false);
      return;
    }

    setPage(1);
    void loadClientData();
  }

  function handleLoadMore() {
    if (page >= totalPages || isLoading) {
      return;
    }

    if (props.mode === 'server') {
      void loadServerPage(page + 1, appliedQuery, true);
      return;
    }

    setPage((currentPage) => currentPage + 1);
  }

  const metaValue = fechaProceso ?? clientMeta?.fecha_proceso ?? null;

  return (
    <section className="consultaMobilePage" data-testid={props.pageTestId}>
      <header className="consultaMobilePage__header">
        <h1>{t(props.pageTitleKey)}</h1>
        {metaValue && props.metaLabelKey && (
          <p className="consultaMobilePage__meta">{t(props.metaLabelKey, { value: metaValue })}</p>
        )}
        {metaValue && !props.metaLabelKey && (
          <p className="consultaMobilePage__meta">{t('consultas.fechaProceso', { value: metaValue })}</p>
        )}
      </header>

      <div className="consultaMobilePage__filterRow">
        <label className="consultaMobilePage__filter">
          <span>{t('consultas.filterSearch')}</span>
          <TextBox
            value={filterQuery}
            width="100%"
            stylingMode="outlined"
            valueChangeEvent="input"
            inputAttr={{ 'data-testid': props.filterTestId ?? 'consultaFilterQ' }}
            onValueChanged={(event) => {
              setFilterQuery(String(event.value ?? ''));
            }}
            onEnterKey={() => {
              setAppliedQuery(filterQuery);
            }}
          />
        </label>
        <Button
          className="consultaMobilePage__refresh"
          icon="refresh"
          stylingMode="outlined"
          hint={t('grid.refresh')}
          disabled={isLoading}
          elementAttr={{ 'data-testid': 'gridRefresh' }}
          onClick={handleRefresh}
        />
      </div>

      {!isLoading && items.length > 0 && (
        <p
          className="consultaMobilePage__resultSummary"
          data-testid={props.resultSummaryTestId ?? 'consultaResultSummary'}
        >
          {t('consultas.resultSummary', { shown: items.length, total: totalItems })}
        </p>
      )}

      {errorKey && (
        <p className="consultaMobilePage__error" data-testid={props.errorTestId ?? 'consultaMobileError'}>
          {t(errorKey)}
        </p>
      )}

      <ConsultaKardexList
        items={items}
        keyExpr={props.keyExpr}
        isLoading={isLoading}
        hasMore={page < totalPages}
        refreshLabel={t('grid.refresh')}
        loadMoreLabel={t('consultas.loadMore')}
        listTestId={props.listTestId}
        emptyMessage={t('consultas.empty')}
        hideToolbar
        onRefresh={handleRefresh}
        onLoadMore={handleLoadMore}
        onItemClick={(item) => {
          setSelectedItem(item);
        }}
        renderCardActions={props.renderCardActions}
        renderCard={props.renderCard}
      />

      <ConsultaDetailPopup
        item={selectedItem}
        title={selectedItem ? props.detailTitle(selectedItem) : ''}
        fields={props.detailFields}
        testId={props.detailPopupTestId}
        footer={
          selectedItem && props.renderDetailFooter
            ? props.renderDetailFooter(selectedItem, () => {
                setSelectedItem(null);
              })
            : undefined
        }
        onClose={() => {
          setSelectedItem(null);
        }}
      />
    </section>
  );
}
