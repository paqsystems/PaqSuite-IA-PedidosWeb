import CustomStore from 'devextreme/data/custom_store';
import DataSource from 'devextreme/data/data_source';
import type { DisposingEvent, InitializedEvent, InputEvent, KeyDownEvent } from 'devextreme/ui/select_box';
import { useCallback, useEffect, useMemo, useRef, useState, type MutableRefObject } from 'react';
import { tryAutoSelectSingleMatch } from '../../../shared/ui/controls/tryAutoSelectSingleMatch';
import { searchArticulos, type ArticuloOption } from '../api/comprobanteApi';
import {
  articulosCargaMinTypedLength,
  articulosCargaSearchTimeoutMs,
  hasEnoughArticulosSearchText,
} from './articulosCargaLoadPolicy';
import {
  createArticulosCargaRemoteLoadState,
  loadArticulosCargaRemote,
  resetArticulosCargaRemoteLoadState,
} from './articulosCargaRemoteLoad';
import { resolveArticulosCargaSearchInput, resolveArticulosCargaSearchText } from './articulosCargaSearchText';

type SelectBoxComponentLike = NonNullable<InitializedEvent['component']>;

const articulosCargaSearchTriggerKeys = new Set(['Tab', 'Enter', 'ArrowDown', 'ArrowUp']);

type ArticulosCargaLoadResult = {
  data: ArticuloOption[];
  totalCount: number;
};

function toArticulosCargaLoadResult(items: ArticuloOption[]): ArticulosCargaLoadResult {
  return {
    data: items,
    totalCount: items.length,
  };
}

async function triggerArticulosCargaSearch(
  component: SelectBoxComponentLike,
  pendingExplicitSearchRef: MutableRefObject<string | null>,
  lastTypedSearchRef: MutableRefObject<string>,
  lastLoadedItemsRef: MutableRefObject<ArticuloOption[]>,
  lastLoadedSearchRef: MutableRefObject<string>,
): Promise<void> {
  const currentSearch = (
    resolveArticulosCargaSearchText(component) || lastTypedSearchRef.current
  ).trim();
  if (!hasEnoughArticulosSearchText(currentSearch)) {
    return;
  }

  const dataSource = component.getDataSource();
  if (!dataSource) {
    return;
  }

  lastLoadedItemsRef.current = [];
  lastLoadedSearchRef.current = '';
  pendingExplicitSearchRef.current = currentSearch;
  component.option('searchValue', currentSearch);

  if (!component.option('opened')) {
    component.open();
  }

  await dataSource.reload();

  const autoSelected = await tryAutoSelectSingleMatch(component, 'codArticulo');
  if (autoSelected === null && !component.option('opened')) {
    component.open();
  }
}

function handleArticulosCargaSearchKeyDown(
  component: SelectBoxComponentLike,
  pendingExplicitSearchRef: MutableRefObject<string | null>,
  lastTypedSearchRef: MutableRefObject<string>,
  lastLoadedItemsRef: MutableRefObject<ArticuloOption[]>,
  lastLoadedSearchRef: MutableRefObject<string>,
  key: string,
  nativeEvent?: KeyboardEvent,
): void {
  if (!articulosCargaSearchTriggerKeys.has(key)) {
    return;
  }

  if (key === 'Enter') {
    nativeEvent?.preventDefault();
  }

  void triggerArticulosCargaSearch(
    component,
    pendingExplicitSearchRef,
    lastTypedSearchRef,
    lastLoadedItemsRef,
    lastLoadedSearchRef,
  );
}

export type ArticulosCargaDataSourceState = {
  dataSource: DataSource | null;
  isLoading: boolean;
  /** Props SelectBox: consulta remota solo al pulsar Tab / Enter / flechas (mín. 4 caracteres). */
  lazySelectBoxOptions: {
    showDataBeforeSearch: false;
    minSearchLength: number;
    searchTimeout: number;
    searchExpr: readonly ['codArticulo', 'descripcion'];
    searchMode: 'contains';
    openOnFieldClick: false;
    onKeyDown: (event: KeyDownEvent) => void;
    onInput: (event: InputEvent) => void;
    onInitialized: (event: InitializedEvent) => void;
    onDisposing: (event: DisposingEvent) => void;
  };
};

export function useArticulosCargaDataSource(
  listaPrecios: number | null | undefined,
): ArticulosCargaDataSourceState {
  const [isLoading, setIsLoading] = useState(false);
  const remoteLoadStateRef = useRef(createArticulosCargaRemoteLoadState());
  const pendingExplicitSearchRef = useRef<string | null>(null);
  const lastTypedSearchRef = useRef('');
  const lastLoadedItemsRef = useRef<ArticuloOption[]>([]);
  const lastLoadedSearchRef = useRef('');
  const fieldKeydownHandlerRef = useRef<((event: KeyboardEvent) => void) | null>(null);

  useEffect(() => {
    pendingExplicitSearchRef.current = null;
    lastTypedSearchRef.current = '';
    lastLoadedItemsRef.current = [];
    lastLoadedSearchRef.current = '';
    resetArticulosCargaRemoteLoadState(remoteLoadStateRef.current);
  }, [listaPrecios]);

  const dataSource = useMemo(() => {
    const codLista = Number(listaPrecios);
    if (listaPrecios === null || listaPrecios === undefined || Number.isNaN(codLista) || codLista <= 0) {
      return null;
    }

    return new DataSource({
      store: new CustomStore({
        key: 'codArticulo',
        cacheRawData: false,
        load: () => {
          const pendingSearch = pendingExplicitSearchRef.current;
          if (pendingSearch === null || !hasEnoughArticulosSearchText(pendingSearch)) {
            if (
              lastLoadedSearchRef.current.length > 0 &&
              lastLoadedItemsRef.current.length > 0
            ) {
              return Promise.resolve(toArticulosCargaLoadResult(lastLoadedItemsRef.current));
            }

            return Promise.resolve(toArticulosCargaLoadResult([]));
          }

          const searchText = pendingSearch.trim();

          return loadArticulosCargaRemote(searchText, codLista, remoteLoadStateRef.current).then(
            (items) => {
              pendingExplicitSearchRef.current = null;
              lastLoadedItemsRef.current = items;
              lastLoadedSearchRef.current = searchText;
              return toArticulosCargaLoadResult(items);
            },
          );
        },
        byKey: (codArticulo) =>
          searchArticulos(String(codArticulo), codLista).then(
            (items) => items.find((item) => item.codArticulo === codArticulo) ?? null,
          ),
      }),
      paginate: false,
      searchExpr: ['codArticulo', 'descripcion'],
      onLoadingChanged: (loading) => {
        setIsLoading(loading === true);
      },
    });
  }, [listaPrecios]);

  const onInput = useCallback((event: InputEvent) => {
    const component = event.component;
    if (!component) {
      return;
    }

    lastTypedSearchRef.current = resolveArticulosCargaSearchText(component);
  }, []);

  const onKeyDown = useCallback((event: KeyDownEvent) => {
    const component = event.component;
    if (!component) {
      return;
    }

    handleArticulosCargaSearchKeyDown(
      component,
      pendingExplicitSearchRef,
      lastTypedSearchRef,
      lastLoadedItemsRef,
      lastLoadedSearchRef,
      event.event?.key ?? '',
      event.event ?? undefined,
    );
  }, []);

  const onInitialized = useCallback((event: InitializedEvent) => {
    const component = event.component;
    if (!component) {
      return;
    }

    const field = resolveArticulosCargaSearchInput(component);
    if (!field) {
      return;
    }

    const handler = (keydownEvent: KeyboardEvent) => {
      handleArticulosCargaSearchKeyDown(
        component,
        pendingExplicitSearchRef,
        lastTypedSearchRef,
        lastLoadedItemsRef,
        lastLoadedSearchRef,
        keydownEvent.key,
        keydownEvent,
      );
    };

    fieldKeydownHandlerRef.current = handler;
    field.addEventListener('keydown', handler);
  }, []);

  const onDisposing = useCallback((event: DisposingEvent) => {
    const component = event.component;
    if (!component) {
      return;
    }

    const field = resolveArticulosCargaSearchInput(component);
    const handler = fieldKeydownHandlerRef.current;
    if (field && handler) {
      field.removeEventListener('keydown', handler);
    }
    fieldKeydownHandlerRef.current = null;
  }, []);

  const lazySelectBoxOptions = useMemo(
    () => ({
      showDataBeforeSearch: false as const,
      minSearchLength: articulosCargaMinTypedLength,
      searchTimeout: articulosCargaSearchTimeoutMs,
      searchExpr: ['codArticulo', 'descripcion'] as const,
      searchMode: 'contains' as const,
      openOnFieldClick: false as const,
      onInput,
      onKeyDown,
      onInitialized,
      onDisposing,
    }),
    [onDisposing, onInitialized, onInput, onKeyDown],
  );

  return { dataSource, isLoading, lazySelectBoxOptions };
}
