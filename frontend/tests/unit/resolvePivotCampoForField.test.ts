import { describe, expect, it } from 'vitest';
import { buildAggregationMenuItems } from '../../src/shared/pivot/utils/pivotAggregationMenu';
import {
  mapDxDataTypeToTipoDato,
  resolvePivotCampoForField,
} from '../../src/shared/pivot/utils/resolvePivotCampoForField';

describe('resolvePivotCampoForField', () => {
  it('usa catálogo cuando el campo existe', () => {
    const campo = resolvePivotCampoForField(
      [
        {
          campoId: 'precio',
          dataField: 'precio',
          caption: 'Precio catálogo',
          tipoDato: 'number',
          rolCampo: 'metrica',
          rolesPermitidos: ['valor'],
          agregacionDefault: 'sum',
          agregacionesPermitidas: ['sum', 'avg', 'min', 'max', 'count'],
          formato: null,
        },
      ],
      'precio',
      { caption: 'Precio UI', dataType: 'string' },
    );

    expect(campo.caption).toBe('Precio catálogo');
    expect(campo.tipoDato).toBe('number');
  });

  it('sintetiza metadata para campos del dataset sin fila en catálogo', () => {
    const campo = resolvePivotCampoForField([], 'nomTransp', {
      caption: 'Transporte',
      dataType: 'string',
    });

    expect(campo.tipoDato).toBe('string');
    expect(campo.agregacionDefault).toBe('count');

    const menuItems = buildAggregationMenuItems({
      campo,
      translate: (key) => key,
      onSelect: () => undefined,
    });

    expect(menuItems.length).toBeGreaterThan(0);
  });

  it('sintetiza number con agregaciones numéricas', () => {
    const campo = resolvePivotCampoForField([], 'precio', {
      caption: 'Precio',
      dataType: 'number',
    });

    expect(campo.tipoDato).toBe('number');
    expect(campo.agregacionDefault).toBe('sum');
  });
});

describe('mapDxDataTypeToTipoDato', () => {
  it('mapea tipos DevExtreme', () => {
    expect(mapDxDataTypeToTipoDato('number')).toBe('number');
    expect(mapDxDataTypeToTipoDato('date')).toBe('date');
    expect(mapDxDataTypeToTipoDato('string')).toBe('string');
  });
});
