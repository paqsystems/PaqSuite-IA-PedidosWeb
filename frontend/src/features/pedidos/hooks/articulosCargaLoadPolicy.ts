/** Mínimo de caracteres tipeados (incluye espacios) para buscar artículos. */
export const articulosCargaMinTypedLength = 4;

/** Pausa sin tipear antes de abrir el desplegable con los resultados. */
export const articulosCargaOpenDropdownDelayMs = 1000;

export function hasEnoughArticulosSearchText(searchValue: string): boolean {
  return searchValue.length >= articulosCargaMinTypedLength;
}

/** Decide si el CustomStore de artículos debe llamar a la API. */
export function shouldFetchArticulosCarga(searchValue: string, allowEmptySearch: boolean): boolean {
  if (hasEnoughArticulosSearchText(searchValue)) {
    return true;
  }

  return allowEmptySearch;
}
