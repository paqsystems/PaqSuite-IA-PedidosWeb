import { beforeEach, describe, expect, it } from 'vitest';
import { IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY } from '../constants';
import type { BorradorFila } from '../types/importacionMasivaTypes';
import {
  clearImportacionMasivaBorradorStorage,
  persistImportacionMasivaBorrador,
  readImportacionMasivaBorradorSnapshot,
  restoreImportacionMasivaBorradorFilas,
} from './importacionMasivaBorradorStorage';

function buildFila(idInterno: string): BorradorFila {
  return {
    idInterno,
    esPedido: true,
    errorGrabacion: null,
    cantidadRenglones: 1,
    totalImporte: 100,
    cabecera: {
      codCliente: 'CLI001',
      razonSocial: 'Cliente Uno',
      codVended: 'V1',
      vendedorNombre: 'Vendedor',
      nivel: 0,
      codCondVta: 1,
      bonif1: 0,
      bonif2: 0,
      bonif3: 0,
      descuento: 0,
      listaPrecios: 1,
      transporte: null,
      direccionEntrega: null,
      perfil: null,
      observaciones: '',
      leyenda1: '',
      leyenda2: '',
      leyenda3: '',
      leyenda4: '',
      leyenda5: '',
      fechaEntrega: null,
      fechaVencimiento: null,
      moneda: 1,
      incluyeIva: false,
    },
    renglones: [
      {
        nroRenglon: 1,
        codArticulo: 'ART1',
        descripcionArticulo: 'Articulo',
        cantidad: 1,
        precio: 100,
        porcBonif: 0,
        porcIva: 21,
        importe: 100,
      },
    ],
  };
}

describe('importacionMasivaBorradorStorage', () => {
  beforeEach(() => {
    sessionStorage.clear();
  });

  it('persiste y restaura el lote completo', () => {
    const filas = [buildFila('f1'), buildFila('f2')];

    persistImportacionMasivaBorrador(filas);

    expect(readImportacionMasivaBorradorSnapshot()?.filas).toHaveLength(2);
    expect(restoreImportacionMasivaBorradorFilas()?.map((fila) => fila.idInterno)).toEqual([
      'f1',
      'f2',
    ]);
  });

  it('limpia sessionStorage cuando no quedan filas', () => {
    persistImportacionMasivaBorrador([buildFila('f1')]);
    persistImportacionMasivaBorrador([]);

    expect(sessionStorage.getItem(IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY)).toBeNull();
    expect(restoreImportacionMasivaBorradorFilas()).toBeNull();
  });

  it('clearImportacionMasivaBorradorStorage elimina el snapshot', () => {
    persistImportacionMasivaBorrador([buildFila('f1')]);
    clearImportacionMasivaBorradorStorage();

    expect(restoreImportacionMasivaBorradorFilas()).toBeNull();
  });
});
