import { describe, expect, it } from 'vitest';
import { getGridDevExtremeMessageOverrides } from './gridDevExtremeMessages';

describe('getGridDevExtremeMessageOverrides', () => {
  it('expone textos del menu contextual de encabezado en espanol', () => {
    const overrides = getGridDevExtremeMessageOverrides('es');

    expect(overrides['dxDataGrid-sortingAscendingText']).toBe('Orden ascendente');
    expect(overrides['dxDataGrid-groupHeaderText']).toBe('Agrupar por esta columna');
    expect(overrides['dxDataGrid-moveColumnToTheLeft']).toBe('Mover a la izquierda');
  });
});
