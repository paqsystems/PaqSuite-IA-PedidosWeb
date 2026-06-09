import { describe, expect, it, vi, beforeEach } from 'vitest';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import * as comprobanteApi from '../api/comprobanteApi';
import { actualizarPreciosRenglonesPorLista } from './actualizarPreciosRenglones';

const renglonActivo: ComprobanteRenglon = {
  renglon: 1,
  codArticulo: 'ART-01',
  descripcionArticulo: 'Articulo test',
  cantidad: 2,
  precio: 50,
  porcBonif: 0,
  porcIva: 21,
};

describe('actualizarPreciosRenglonesPorLista', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('actualiza precio de renglones con artículo según la lista indicada', async () => {
    vi.spyOn(comprobanteApi, 'fetchPreciosArticulosPorLista').mockResolvedValue([
      { codArticulo: 'ART-01', precio: 120 },
    ]);

    const resultado = await actualizarPreciosRenglonesPorLista([renglonActivo], 3);

    expect(resultado[0].precio).toBe(120);
    expect(comprobanteApi.fetchPreciosArticulosPorLista).toHaveBeenCalledWith(['ART-01'], 3);
  });

  it('no altera renglones vacíos', async () => {
    const vacio: ComprobanteRenglon = { ...renglonActivo, codArticulo: '', renglon: 2 };
    const fetchSpy = vi.spyOn(comprobanteApi, 'fetchPreciosArticulosPorLista').mockResolvedValue([
      { codArticulo: 'ART-01', precio: 120 },
    ]);

    const resultado = await actualizarPreciosRenglonesPorLista([renglonActivo, vacio], 3);

    expect(resultado[1]).toBe(vacio);
    expect(fetchSpy).toHaveBeenCalledWith(['ART-01'], 3);
  });
});
