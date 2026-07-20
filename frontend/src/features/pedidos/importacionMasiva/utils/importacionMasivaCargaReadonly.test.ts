import { describe, expect, it } from 'vitest';
import { emptyComprobanteCabecera } from '../../types/comprobanteCabecera';
import {
  buildImportacionMasivaCargaHydration,
  parseImportacionMasivaCargaState,
} from './importacionMasivaCargaReadonly';

describe('importacionMasivaCargaReadonly', () => {
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
