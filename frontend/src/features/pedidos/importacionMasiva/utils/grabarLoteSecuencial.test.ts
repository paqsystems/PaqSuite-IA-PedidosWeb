import { describe, expect, it, vi } from 'vitest';
import type { BorradorFila } from '../types/importacionMasivaTypes';
import { grabarLoteSecuencial } from './grabarLoteSecuencial';

vi.mock('../../api/comprobanteApi', () => ({
  grabarComprobante: vi.fn(),
}));

import { grabarComprobante } from '../../api/comprobanteApi';

const t = ((key: string) => key) as never;

function buildFila(idInterno: string): BorradorFila {
  return {
    idInterno,
    esPedido: true,
    errorGrabacion: null,
    cantidadRenglones: 1,
    totalImporte: 100,
    cabecera: {
      codCliente: 'CLI001',
      codVended: 'V1',
      vendedorNombre: 'Vendedor',
      codCondvta: 1,
      codTranspor: null,
      idDe: null,
      direccionEntrega: '',
      expreso: null,
      expresoDire: null,
      nivel: 0,
      listaPrecios: 1,
      listaPreciosDescripcion: '',
      moneda: 1,
      incluyeIva: false,
      bonif1: 0,
      bonif2: 0,
      bonif3: 0,
      descuento: 0,
      observaciones: '',
      codPerfil: 'P1',
      leyenda1: null,
      leyenda2: null,
      leyenda3: null,
      leyenda4: null,
      leyenda5: null,
      fechaEntrega: null,
    },
    renglones: [
      {
        renglon: 1,
        codArticulo: 'ART1',
        descripcionArticulo: 'Articulo',
        cantidad: 1,
        precio: 100,
        porcBonif: 0,
        porcIva: 21,
      },
    ],
  };
}

describe('grabarLoteSecuencial', () => {
  it('procesa secuencialmente y continua tras error', async () => {
    vi.mocked(grabarComprobante)
      .mockResolvedValueOnce({ resultado: { nro_visible: 1 } })
      .mockRejectedValueOnce(new Error('fallo'));

    const progreso: Array<{ x: number; n: number } | null> = [];
    const okIds: string[] = [];
    const errIds: string[] = [];

    const resumen = await grabarLoteSecuencial([buildFila('a'), buildFila('b')], t, {
      onProgreso: (value) => progreso.push(value),
      onFilaOk: (idInterno) => okIds.push(idInterno),
      onFilaError: (idInterno) => errIds.push(idInterno),
    });

    expect(resumen).toEqual({ ok: 1, err: 1 });
    expect(okIds).toEqual(['a']);
    expect(errIds).toEqual(['b']);
    expect(progreso.at(-1)).toBeNull();
  });
});
