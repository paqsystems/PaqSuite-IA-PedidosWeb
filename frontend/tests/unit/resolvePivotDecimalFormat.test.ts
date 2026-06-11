import { describe, expect, it } from 'vitest';
import { mapMetadataToPivotFields } from '../../src/shared/pivot/utils/mapMetadataToPivotFields';
import {
  applyPivotNumberFieldFormat,
  pivotDecimalDxFormat,
  resolvePivotCampoFormatoMetadata,
  resolvePivotFieldFormat,
} from '../../src/shared/pivot/utils/resolvePivotDecimalFormat';
import { resolvePivotCampoForField } from '../../src/shared/pivot/utils/resolvePivotCampoForField';

describe('resolvePivotDecimalFormat', () => {
  it('number siempre usa #,##0.00', () => {
    expect(resolvePivotFieldFormat('number', null)).toBe(pivotDecimalDxFormat);
    expect(resolvePivotFieldFormat('number', { format: '#,##0' })).toBe(pivotDecimalDxFormat);
  });

  it('string no aplica decimal', () => {
    expect(resolvePivotFieldFormat('string', null)).toBeUndefined();
  });

  it('applyPivotNumberFieldFormat fuerza format en field config', () => {
    const field: { dataType?: string; format?: string } = { dataType: 'number' };

    applyPivotNumberFieldFormat(field, 'number');

    expect(field.format).toBe('#,##0.00');
  });

  it('mapMetadataToPivotFields propaga formato decimal', () => {
    const fields = mapMetadataToPivotFields([
      {
        campoId: 'saldo',
        dataField: 'saldo',
        caption: 'Saldo',
        tipoDato: 'number',
        rolCampo: 'metrica',
        rolesPermitidos: ['valor'],
        agregacionDefault: 'sum',
        agregacionesPermitidas: ['sum'],
        formato: null,
      },
    ]);

    expect(fields[0]?.format).toBe('#,##0.00');
  });

  it('campo sintético number trae formato metadata', () => {
    const campo = resolvePivotCampoForField([], 'precio', { dataType: 'number' });

    expect(campo.formato).toEqual(resolvePivotCampoFormatoMetadata('number'));
  });
});
