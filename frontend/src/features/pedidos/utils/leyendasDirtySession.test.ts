import { describe, expect, it } from 'vitest';
import { emptyComprobanteCabecera } from '../types/comprobanteCabecera';
import {
  computeLeyendasDirtyFlags,
  createLeyendasSnapshot,
  mapLeyendasDirtyToApi,
} from './leyendasDirtySession';

describe('leyendasDirtySession', () => {
  it('crea snapshot con leyendas de cabecera', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    cabecera.leyenda1 = 'Entrega Folletería';
    cabecera.leyenda2 = 'Obs';

    const snapshot = createLeyendasSnapshot(cabecera);

    expect(snapshot).toEqual({
      leyenda1: 'Entrega Folletería',
      leyenda2: 'Obs',
      leyenda3: null,
      leyenda4: null,
      leyenda5: null,
    });
  });

  it('marca dirty solo las leyendas modificadas en sesión', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    cabecera.leyenda1 = 'Original';
    cabecera.leyenda2 = 'Sin cambio';

    const snapshot = createLeyendasSnapshot(cabecera);
    cabecera.leyenda1 = '  Editada  ';

    const flags = computeLeyendasDirtyFlags(cabecera, snapshot);

    expect(flags.leyenda1).toBe(true);
    expect(flags.leyenda2).toBe(false);
  });

  it('sin snapshot no marca ninguna leyenda dirty', () => {
    const cabecera = emptyComprobanteCabecera('CLI001');
    cabecera.leyenda1 = 'X';

    const flags = computeLeyendasDirtyFlags(cabecera, null);

    expect(flags).toEqual({
      leyenda1: false,
      leyenda2: false,
      leyenda3: false,
      leyenda4: false,
      leyenda5: false,
    });
  });

  it('mapea flags dirty al payload API snake_case', () => {
    expect(
      mapLeyendasDirtyToApi({
        leyenda1: true,
        leyenda2: false,
        leyenda3: true,
        leyenda4: false,
        leyenda5: false,
      }),
    ).toEqual({
      leyenda_1_dirty: true,
      leyenda_2_dirty: false,
      leyenda_3_dirty: true,
      leyenda_4_dirty: false,
      leyenda_5_dirty: false,
    });
  });
});
