import { describe, expect, it } from 'vitest';
import { resolveDxSelectBoxLoadState } from './useDxSelectBoxLoadState';

describe('resolveDxSelectBoxLoadState', () => {
  it('deshabilita y expone hint durante la carga', () => {
    expect(resolveDxSelectBoxLoadState(true, 'Cargando…')).toEqual({
      disabled: true,
      hint: 'Cargando…',
    });
  });

  it('habilita y no fuerza hint cuando no hay carga', () => {
    expect(resolveDxSelectBoxLoadState(false, 'Cargando…')).toEqual({
      disabled: false,
      hint: undefined,
    });
  });

  it('no deshabilita si disableWhileLoading es false', () => {
    expect(resolveDxSelectBoxLoadState(true, 'Cargando…', false)).toEqual({
      disabled: false,
      hint: 'Cargando…',
    });
  });
});
