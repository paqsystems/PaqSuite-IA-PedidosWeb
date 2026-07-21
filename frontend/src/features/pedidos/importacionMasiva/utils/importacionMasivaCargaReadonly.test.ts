import { afterEach, describe, expect, it } from 'vitest';
import { emptyComprobanteCabecera } from '../../types/comprobanteCabecera';
import {
  buildImportacionMasivaCargaHydration,
  parseImportacionMasivaCargaState,
  readImportacionMasivaConsultPayload,
  resolveImportacionMasivaCargaContext,
  storeImportacionMasivaConsultPayload,
} from './importacionMasivaCargaReadonly';

describe('importacionMasivaCargaReadonly', () => {
  afterEach(() => {
    localStorage.clear();
  });

  it('parsea state valido de consulta readonly', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    const parsed = parseImportacionMasivaCargaState({
      mode: 'readonly',
      from: 'importacionMasiva',
      borrador: {
        idInterno: 'f1',
        esPedido: false,
        cabecera,
        renglones: [],
      },
    });

    expect(parsed).not.toBeNull();
    expect(parsed?.borrador.idInterno).toBe('f1');
    expect(parsed?.borrador.esPedido).toBe(false);
  });

  it('rechaza state invalido o incompleto', () => {
    expect(parseImportacionMasivaCargaState(null)).toBeNull();
    expect(parseImportacionMasivaCargaState({ mode: 'edit' })).toBeNull();
    expect(
      parseImportacionMasivaCargaState({
        mode: 'readonly',
        from: 'importacionMasiva',
        borrador: { idInterno: 'f1' },
      }),
    ).toBeNull();
  });

  it('persiste y lee consulta entre solapas via localStorage', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    const state = {
      mode: 'readonly' as const,
      from: 'importacionMasiva' as const,
      borrador: {
        idInterno: 'f1',
        esPedido: true,
        cabecera,
        renglones: [],
      },
    };

    const key = storeImportacionMasivaConsultPayload(state);
    const first = readImportacionMasivaConsultPayload(key);
    const second = readImportacionMasivaConsultPayload(key);

    expect(first?.borrador.idInterno).toBe('f1');
    expect(second?.borrador.idInterno).toBe('f1');
    expect(localStorage.getItem(key)).not.toBeNull();
  });

  it('acepta codCliente numerico al parsear state de consulta', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    const parsed = parseImportacionMasivaCargaState({
      mode: 'readonly',
      from: 'importacionMasiva',
      borrador: {
        idInterno: 'f-num',
        esPedido: true,
        cabecera: { ...cabecera, codCliente: 12345 as unknown as string },
        renglones: [],
      },
    });

    expect(parsed?.borrador.cabecera.codCliente).toBe('12345');
  });

  it('resuelve contexto desde query de consulta', () => {
    const cabecera = emptyComprobanteCabecera('CLI009');
    const key = storeImportacionMasivaConsultPayload({
      mode: 'readonly',
      from: 'importacionMasiva',
      borrador: {
        idInterno: 'f9',
        esPedido: false,
        cabecera,
        renglones: [],
      },
    });

    const resolved = resolveImportacionMasivaCargaContext({
      locationState: null,
      consultStorageKey: key,
    });

    expect(resolved?.borrador.idInterno).toBe('f9');
  });

  it('arma hidratacion de carga sin fetch de comprobante', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    const hydration = buildImportacionMasivaCargaHydration({
      idInterno: 'f1',
      esPedido: true,
      cabecera,
      renglones: [
        {
          nroRenglon: 1,
          codArticulo: 'ART1',
          descripcionArticulo: 'Articulo',
          cantidad: 2,
          precio: 10,
          porcBonif: 0,
          porcIva: 21,
          importe: 20,
        },
      ],
    });

    expect(hydration.selectedCliente).toBe('CLI001');
    expect(hydration.estadoActual).toBe(0);
    expect(hydration.renglones).toHaveLength(1);
    expect(hydration.renglones[0]?.codArticulo).toBe('ART1');
  });

  it('usa presupuesto cuando esPedido es false', () => {
    const hydration = buildImportacionMasivaCargaHydration({
      idInterno: 'f2',
      esPedido: false,
      cabecera: emptyComprobanteCabecera('CLI002'),
      renglones: [],
    });

    expect(hydration.estadoActual).toBe(99);
    expect(hydration.renglones).toHaveLength(1);
  });
});
