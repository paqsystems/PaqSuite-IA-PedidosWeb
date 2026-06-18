import { searchArticulos, type ArticuloOption } from '../api/comprobanteApi';
import { shouldFetchArticulosCarga } from './articulosCargaLoadPolicy';

export const articulosCargaFailedQueryCooldownMs = 3000;

export function buildArticulosCargaLoadKey(codLista: number, searchValue: string): string {
  return `${codLista}:${searchValue.trim()}`;
}

export type ArticulosCargaRemoteLoadState = {
  inflightLoads: Map<string, Promise<ArticuloOption[]>>;
  failedQueryAt: Map<string, number>;
};

export function createArticulosCargaRemoteLoadState(): ArticulosCargaRemoteLoadState {
  return {
    inflightLoads: new Map(),
    failedQueryAt: new Map(),
  };
}

export function resetArticulosCargaRemoteLoadState(state: ArticulosCargaRemoteLoadState): void {
  state.inflightLoads.clear();
  state.failedQueryAt.clear();
}

export async function loadArticulosCargaRemote(
  rawSearchValue: string,
  codLista: number,
  state: ArticulosCargaRemoteLoadState,
): Promise<ArticuloOption[]> {
  if (!shouldFetchArticulosCarga(rawSearchValue)) {
    return [];
  }

  const loadKey = buildArticulosCargaLoadKey(codLista, rawSearchValue);
  const inflight = state.inflightLoads.get(loadKey);
  if (inflight) {
    return inflight;
  }

  const failedAt = state.failedQueryAt.get(loadKey);
  if (failedAt !== undefined && Date.now() - failedAt < articulosCargaFailedQueryCooldownMs) {
    return [];
  }

  const promise = searchArticulos(rawSearchValue.trim(), codLista, articulosCargaPageSize)
    .catch(() => {
      state.failedQueryAt.set(loadKey, Date.now());
      return [] as ArticuloOption[];
    })
    .finally(() => {
      state.inflightLoads.delete(loadKey);
    });

  state.inflightLoads.set(loadKey, promise);
  return promise;
}
