import { describe, expect, it } from 'vitest';
import { buildAggregationMenuItems } from '../../src/shared/pivot/utils/pivotAggregationMenu';
import {
  resolvePivotAllowedAggregations,
  resolvePivotDefaultSummaryType,
} from '../../src/shared/pivot/utils/resolvePivotAggregations';

const baseCampo = {
  campoId: 'x',
  dataField: 'x',
  caption: 'Campo',
  rolCampo: 'dimension',
  rolesPermitidos: [],
  agregacionDefault: null,
  agregacionesPermitidas: null,
  formato: null,
};

describe('resolvePivotAllowedAggregations', () => {
  it('string: count, min y max', () => {
    const allowed = resolvePivotAllowedAggregations({ ...baseCampo, tipoDato: 'string' });

    expect(allowed).toEqual(['count', 'min', 'max']);
  });

  it('number: sum, avg, min, max y count', () => {
    const allowed = resolvePivotAllowedAggregations({ ...baseCampo, tipoDato: 'number' });

    expect(allowed).toEqual(['sum', 'avg', 'min', 'max', 'count']);
  });

  it('usa agregacionesPermitidas de metadata cuando vienen del backend', () => {
    const allowed = resolvePivotAllowedAggregations({
      ...baseCampo,
      tipoDato: 'number',
      agregacionesPermitidas: ['sum', 'avg', 'min', 'max', 'count'],
    });

    expect(allowed).toEqual(['sum', 'avg', 'min', 'max', 'count']);
  });

  it('fallback por tipo si metadata no trae agregaciones', () => {
    const menuItems = buildAggregationMenuItems({
      campo: {
        ...baseCampo,
        tipoDato: 'string',
        caption: 'Cliente',
        agregacionesPermitidas: [],
      },
      translate: (key) => key,
      onSelect: () => undefined,
    });

    expect(menuItems.map((item) => item.text)).toEqual([
      'pivot.aggregation.min',
      'pivot.aggregation.max',
      'pivot.aggregation.count',
    ]);
  });
});

describe('resolvePivotDefaultSummaryType', () => {
  it('string usa count por defecto', () => {
    expect(resolvePivotDefaultSummaryType({ ...baseCampo, tipoDato: 'string' })).toBe('count');
  });

  it('number respeta agregacionDefault si es valida', () => {
    expect(
      resolvePivotDefaultSummaryType({
        ...baseCampo,
        tipoDato: 'number',
        agregacionDefault: 'avg',
      }),
    ).toBe('avg');
  });
});
