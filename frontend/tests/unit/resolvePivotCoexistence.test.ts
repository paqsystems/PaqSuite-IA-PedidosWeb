import { describe, expect, it } from 'vitest';
import {
  canCoexistGridAndPivot,
  shouldShowPivotOnly,
} from '../../src/shared/pivot/utils/resolvePivotCoexistence';

describe('resolvePivotCoexistence', () => {
  it('habilita convivencia para proceso informe', () => {
    expect(
      canCoexistGridAndPivot({
        pivotsEnabled: true,
        pivotHabilitado: true,
        tipoProceso: 'informe',
      }),
    ).toBe(true);
  });

  it('habilita convivencia por mostrarGrillaYPivot', () => {
    expect(
      canCoexistGridAndPivot({
        pivotsEnabled: true,
        pivotHabilitado: true,
        tipoProceso: 'P',
        mostrarGrillaYPivot: true,
      }),
    ).toBe(true);
  });

  it('no habilita convivencia si pivotsEnabled es false', () => {
    expect(
      canCoexistGridAndPivot({
        pivotsEnabled: false,
        pivotHabilitado: true,
        tipoProceso: 'informe',
      }),
    ).toBe(false);
  });

  it('detecta modo solo pivot', () => {
    expect(
      shouldShowPivotOnly({
        pivotsEnabled: true,
        pivotHabilitado: true,
        tipoProceso: 'P',
        mostrarGrillaYPivot: false,
      }),
    ).toBe(true);
  });
});
