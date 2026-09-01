import { describe, expect, it } from 'vitest';
import { emptyComprobanteCabecera } from '../../types/comprobanteCabecera';
import { leyendaMaxCaracteres } from '../../utils/recortarLeyendaCabecera';
import { patchAsistenteCabecera } from './patchAsistenteCabecera';

describe('patchAsistenteCabecera', () => {
  it('recorta leyendas a 60 caracteres', () => {
    const patched = patchAsistenteCabecera(emptyComprobanteCabecera('CLI001'), {
      leyenda1: 'x'.repeat(leyendaMaxCaracteres + 1),
      observaciones: 'queda igual',
    });

    expect(patched.leyenda1).toBe('x'.repeat(leyendaMaxCaracteres));
    expect(patched.observaciones).toBe('queda igual');
  });

  it('conserva leyenda de exactamente 60 caracteres', () => {
    const exacto = 'y'.repeat(leyendaMaxCaracteres);
    const patched = patchAsistenteCabecera(emptyComprobanteCabecera('CLI001'), {
      leyenda2: exacto,
    });

    expect(patched.leyenda2).toBe(exacto);
  });
});
