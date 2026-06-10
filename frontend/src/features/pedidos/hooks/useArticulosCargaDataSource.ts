import CustomStore from 'devextreme/data/custom_store';
import DataSource from 'devextreme/data/data_source';
import { useMemo } from 'react';
import { searchArticulos } from '../api/comprobanteApi';

export function useArticulosCargaDataSource(listaPrecios: number | null | undefined): DataSource | null {
  return useMemo(() => {
    if (listaPrecios === null || listaPrecios === undefined || listaPrecios <= 0) {
      return null;
    }

    const codLista = listaPrecios;

    return new DataSource({
      store: new CustomStore({
        key: 'codArticulo',
        load: (loadOptions) => {
          const searchValue =
            typeof loadOptions.searchValue === 'string' ? loadOptions.searchValue.trim() : '';

          return searchArticulos(searchValue, codLista);
        },
        byKey: (codArticulo) =>
          searchArticulos(String(codArticulo), codLista).then(
            (items) => items.find((item) => item.codArticulo === codArticulo) ?? null,
          ),
      }),
      paginate: false,
    });
  }, [listaPrecios]);
}
