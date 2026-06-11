import { describe, expect, it } from 'vitest';
import { normalizePivotStoreRecords } from '../../src/shared/pivot/utils/normalizePivotStoreRecords';
import { resolvePivotDateFormat } from '../../src/shared/pivot/utils/resolvePivotDateFormat';
import { resolvePivotDetailDimensionField } from '../../src/shared/pivot/utils/resolvePivotDetailDimensionField';

describe('resolvePivotDateFormat', () => {
  it('resuelve formato de fecha segun locale', () => {
    expect(resolvePivotDateFormat('es-AR')).toBe('dd/MM/yyyy');
    expect(resolvePivotDateFormat('en-US')).toBe('MM/dd/yyyy');
    expect(resolvePivotDateFormat('pt')).toBe('dd/MM/yyyy');
  });
});

describe('normalizePivotStoreRecords', () => {
  it('convierte strings ISO en Date para campos date', () => {
    const rows = normalizePivotStoreRecords(
      [{ fechaEmision: '2026-06-03T00:00:00.000Z', codCliente: '1' }],
      [
        {
          campoId: 'fechaEmision',
          dataField: 'fechaEmision',
          caption: 'Fecha',
          tipoDato: 'date',
          rolCampo: 'dimension',
          agregacionDefault: null,
          agregacionesPermitidas: null,
          formato: null,
        },
      ],
    );

    expect(rows[0]?.fechaEmision).toBeInstanceOf(Date);
  });
});

describe('resolvePivotDetailDimensionField', () => {
  it('sugiere razon social detras de cod cliente', () => {
    const dataSource = {
      fields: () => [{ area: 'row', dataField: 'codCliente' }],
    };

    const detailField = resolvePivotDetailDimensionField(
      [
        {
          campoId: 'codCliente',
          dataField: 'codCliente',
          caption: 'Cliente',
          tipoDato: 'string',
          rolCampo: 'dimension',
          agregacionDefault: null,
          agregacionesPermitidas: null,
          formato: null,
        },
        {
          campoId: 'razonSocial',
          dataField: 'razonSocial',
          caption: 'Razon social',
          tipoDato: 'string',
          rolCampo: 'dimension',
          agregacionDefault: null,
          agregacionesPermitidas: null,
          formato: null,
        },
      ],
      dataSource as never,
      'codCliente',
    );

    expect(detailField?.dataField).toBe('razonSocial');
  });

  it('no sugiere fechaEmision si razonSocial ya esta en filas', () => {
    const dataSource = {
      fields: () => [
        { area: 'row', dataField: 'codCliente' },
        { area: 'row', dataField: 'razonSocial' },
      ],
    };

    const detailField = resolvePivotDetailDimensionField(
      [
        {
          campoId: 'codCliente',
          dataField: 'codCliente',
          caption: 'Cliente',
          tipoDato: 'string',
          rolCampo: 'dimension',
          agregacionDefault: null,
          agregacionesPermitidas: null,
          formato: null,
        },
        {
          campoId: 'razonSocial',
          dataField: 'razonSocial',
          caption: 'Razon social',
          tipoDato: 'string',
          rolCampo: 'dimension',
          agregacionDefault: null,
          agregacionesPermitidas: null,
          formato: null,
        },
        {
          campoId: 'fechaEmision',
          dataField: 'fechaEmision',
          caption: 'Fecha emision',
          tipoDato: 'date',
          rolCampo: 'dimension',
          agregacionDefault: null,
          agregacionesPermitidas: null,
          formato: null,
        },
      ],
      dataSource as never,
      'codCliente',
    );

    expect(detailField).toBeUndefined();
  });
});
