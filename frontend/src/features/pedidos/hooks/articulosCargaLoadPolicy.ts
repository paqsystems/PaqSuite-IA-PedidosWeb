/** Mínimo de caracteres tipeados (incluye espacios) para buscar artículos. */
export const articulosCargaMinTypedLength = 4;

/**
 * DevExtreme dispara búsqueda tras `searchTimeout` ms; valor alto evita consultas al tipear.
 * La API solo se llama con disparo explícito (Tab / Enter / flechas).
 */
export const articulosCargaSearchTimeoutMs = 86_400_000;

export function hasEnoughArticulosSearchText(searchValue: string): boolean {
  return searchValue.length >= articulosCargaMinTypedLength;
}

/** Hay una consulta explícita pendiente (Tab / Enter / flechas). */
export function hasPendingArticulosCargaQuery(pendingSearch: string | null): boolean {
  return pendingSearch !== null && hasEnoughArticulosSearchText(pendingSearch);
}

/** Decide si el CustomStore de artículos debe llamar a la API (solo con texto de búsqueda). */
export function shouldFetchArticulosCarga(searchValue: string): boolean {
  return hasEnoughArticulosSearchText(searchValue);
}
