import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
  articulosCargaFailedQueryCooldownMs,
  createArticulosCargaRemoteLoadState,
  loadArticulosCargaRemote,
  resetArticulosCargaRemoteLoadState,
} from './articulosCargaRemoteLoad';

const searchArticulosMock = vi.hoisted(() => vi.fn());

vi.mock('../api/comprobanteApi', () => ({
  searchArticulos: searchArticulosMock,
}));

describe('loadArticulosCargaRemote', () => {
  beforeEach(() => {
    searchArticulosMock.mockReset();
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('deduplica peticiones concurrentes con la misma clave', async () => {
    let resolveSearch: (value: { codArticulo: string }[]) => void = () => undefined;
    const pendingSearch = new Promise<{ codArticulo: string }[]>((resolve) => {
      resolveSearch = resolve;
    });

    searchArticulosMock.mockReturnValue(pendingSearch);

    const state = createArticulosCargaRemoteLoadState();
    const first = loadArticulosCargaRemote('tosta', 4, state);
    const second = loadArticulosCargaRemote('tosta', 4, state);

    expect(searchArticulosMock).toHaveBeenCalledTimes(1);

    resolveSearch([{ codArticulo: 'A1' }]);

    await expect(first).resolves.toEqual([{ codArticulo: 'A1' }]);
    await expect(second).resolves.toEqual([{ codArticulo: 'A1' }]);
  });

  it('devuelve arreglo vacío ante error y aplica cooldown antes de reintentar', async () => {
    searchArticulosMock.mockRejectedValue(new Error('timeout'));

    const state = createArticulosCargaRemoteLoadState();
    await expect(loadArticulosCargaRemote('tosta', 4, state)).resolves.toEqual([]);
    expect(searchArticulosMock).toHaveBeenCalledTimes(1);

    await expect(loadArticulosCargaRemote('tosta', 4, state)).resolves.toEqual([]);
    expect(searchArticulosMock).toHaveBeenCalledTimes(1);

    vi.advanceTimersByTime(articulosCargaFailedQueryCooldownMs);

    searchArticulosMock.mockResolvedValue([{ codArticulo: 'A1' }]);
    await expect(loadArticulosCargaRemote('tosta', 4, state)).resolves.toEqual([
      { codArticulo: 'A1' },
    ]);
    expect(searchArticulosMock).toHaveBeenCalledTimes(2);
  });

  it('resetea estado al cambiar lista de precios', async () => {
    searchArticulosMock.mockRejectedValue(new Error('timeout'));

    const state = createArticulosCargaRemoteLoadState();
    await loadArticulosCargaRemote('tosta', 4, state);

    resetArticulosCargaRemoteLoadState(state);
    searchArticulosMock.mockResolvedValue([{ codArticulo: 'A1' }]);

    await expect(loadArticulosCargaRemote('tosta', 4, state)).resolves.toEqual([
      { codArticulo: 'A1' },
    ]);
    expect(searchArticulosMock).toHaveBeenCalledTimes(2);
  });
});
