import { describe, expect, it } from 'vitest';
import { getSummaryTypesForDataType, resolveSummaryDataType } from './dataGridSummaryContextMenu';

describe('dataGridSummaryContextMenu', () => {
  it('resuelve tipos de dato de columna', () => {
    expect(resolveSummaryDataType('number')).toBe('numeric');
    expect(resolveSummaryDataType('date')).toBe('date');
    expect(resolveSummaryDataType('string')).toBe('string');
  });

  it('ofrece totalizadores segun tipo de dato', () => {
    expect(getSummaryTypesForDataType('numeric')).toEqual(['sum', 'avg', 'min', 'max', 'count']);
    expect(getSummaryTypesForDataType('date')).toEqual(['min', 'max', 'count']);
    expect(getSummaryTypesForDataType('string')).toEqual(['count', 'min', 'max']);
  });
});
