import { describe, expect, it } from 'vitest';
import { resolvePivotDataFieldIndex } from '../../src/shared/pivot/utils/resolvePivotDataFieldIndex';

describe('resolvePivotDataFieldIndex', () => {
  it('encuentra el indice por dataField', () => {
    const index = resolvePivotDataFieldIndex(
      [{ dataField: 'codCliente' }, { dataField: 'cantidad' }],
      'cantidad',
    );

    expect(index).toBe(1);
  });

  it('devuelve -1 si no existe el campo', () => {
    const index = resolvePivotDataFieldIndex([{ dataField: 'codCliente' }], 'inexistente');

    expect(index).toBe(-1);
  });
});
