import CustomStore from 'devextreme/data/custom_store';

import DataSource from 'devextreme/data/data_source';

import type { ClosedEvent, InputEvent, OpenedEvent } from 'devextreme/ui/select_box';

import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

import { searchArticulos } from '../api/comprobanteApi';

import {

  articulosCargaMinTypedLength,

  articulosCargaOpenDropdownDelayMs,

  hasEnoughArticulosSearchText,

  shouldFetchArticulosCarga,

} from './articulosCargaLoadPolicy';



type SelectBoxComponentLike = InputEvent['component'];



function resolveRawSelectBoxText(component: SelectBoxComponentLike): string {

  const searchValue = component.option('searchValue');

  if (typeof searchValue === 'string') {

    return searchValue;

  }



  const text = component.option('text');

  return typeof text === 'string' ? text : '';

}



export type ArticulosCargaDataSourceState = {

  dataSource: DataSource | null;

  isLoading: boolean;

  /** Props SelectBox: no consulta API hasta escribir o abrir con la flecha del desplegable. */

  lazySelectBoxOptions: {

    showDataBeforeSearch: false;

    minSearchLength: number;

    openOnFieldClick: false;

    onOpened: (event: OpenedEvent) => void;

    onClosed: (event: ClosedEvent) => void;

    onInput: (event: InputEvent) => void;

  };

};



export function useArticulosCargaDataSource(

  listaPrecios: number | null | undefined,

): ArticulosCargaDataSourceState {

  const [isLoading, setIsLoading] = useState(false);

  const allowEmptySearchRef = useRef(false);

  const openDropdownTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);



  useEffect(

    () => () => {

      if (openDropdownTimerRef.current) {

        clearTimeout(openDropdownTimerRef.current);

      }

    },

    [],

  );



  const dataSource = useMemo(() => {

    const codLista = Number(listaPrecios);

    if (listaPrecios === null || listaPrecios === undefined || Number.isNaN(codLista) || codLista <= 0) {

      allowEmptySearchRef.current = false;

      return null;

    }



    allowEmptySearchRef.current = false;



    return new DataSource({

      store: new CustomStore({

        key: 'codArticulo',

        cacheRawData: false,

        load: (loadOptions) => {

          const rawSearchValue =

            typeof loadOptions.searchValue === 'string' ? loadOptions.searchValue : '';



          if (!shouldFetchArticulosCarga(rawSearchValue, allowEmptySearchRef.current)) {

            return Promise.resolve([]);

          }



          return searchArticulos(rawSearchValue.trim(), codLista);

        },

        byKey: (codArticulo) =>

          searchArticulos(String(codArticulo), codLista).then(

            (items) => items.find((item) => item.codArticulo === codArticulo) ?? null,

          ),

      }),

      paginate: false,

      onLoadingChanged: (loading) => {

        setIsLoading(loading === true);

      },

    });

  }, [listaPrecios]);



  const onOpened = useCallback((event: OpenedEvent) => {

    const currentSearch = resolveRawSelectBoxText(event.component);



    if (hasEnoughArticulosSearchText(currentSearch)) {

      return;

    }



    allowEmptySearchRef.current = true;

    queueMicrotask(() => {

      void event.component.getDataSource()?.reload();

    });

  }, []);



  const onClosed = useCallback(() => {

    allowEmptySearchRef.current = false;

    if (openDropdownTimerRef.current) {

      clearTimeout(openDropdownTimerRef.current);

      openDropdownTimerRef.current = null;

    }

  }, []);



  const onInput = useCallback((event: InputEvent) => {

    const component = event.component;



    if (openDropdownTimerRef.current) {

      clearTimeout(openDropdownTimerRef.current);

    }



    openDropdownTimerRef.current = setTimeout(() => {

      const currentSearch = resolveRawSelectBoxText(component);

      if (!hasEnoughArticulosSearchText(currentSearch)) {

        return;

      }



      if (!component.option('opened')) {

        component.open();

      }

    }, articulosCargaOpenDropdownDelayMs);

  }, []);



  const lazySelectBoxOptions = useMemo(

    () => ({

      showDataBeforeSearch: false as const,

      minSearchLength: articulosCargaMinTypedLength,

      openOnFieldClick: false as const,

      onOpened,

      onClosed,

      onInput,

    }),

    [onClosed, onOpened, onInput],

  );



  return { dataSource, isLoading, lazySelectBoxOptions };

}


