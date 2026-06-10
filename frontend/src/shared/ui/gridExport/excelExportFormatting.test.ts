import { describe, expect, it } from 'vitest';
import {
  formatBooleanExportValue,
  isIntegerColumnFormat,
  resolveDecimalNumFmt,
  resolveExcelDateNumFmt,
} from './excelExportFormatting';

describe('excelExportFormatting', () => {
  it('resuelve formatos de fecha segun locale', () => {
    expect(resolveExcelDateNumFmt('date', 'es-AR')).toBe('dd/mm/yyyy');
    expect(resolveExcelDateNumFmt('datetime', 'en-US')).toBe('mm/dd/yyyy hh:mm');
  });

  it('resuelve decimales desde format de columna', () => {
    expect(resolveDecimalNumFmt('#,##0.00')).toBe('0.00');
    expect(resolveDecimalNumFmt('#0.##%')).toBe('0%');
    expect(resolveDecimalNumFmt('#,##0')).toBe('0');
  });

  it('detecta columnas enteras', () => {
    expect(isIntegerColumnFormat('#,##0')).toBe(true);
    expect(isIntegerColumnFormat('#,##0.00')).toBe(false);
  });

  it('formatea booleanos con etiquetas i18n', () => {
    const labels = { trueLabel: 'VERDADERO', falseLabel: 'FALSO' };

    expect(formatBooleanExportValue(true, labels)).toBe('VERDADERO');
    expect(formatBooleanExportValue(false, labels)).toBe('FALSO');
    expect(formatBooleanExportValue('x', labels)).toBeNull();
  });
});
