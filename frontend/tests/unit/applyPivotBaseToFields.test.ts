import { describe, expect, it } from 'vitest';
import { applyPivotBaseToFields } from '../../src/shared/pivot/utils/applyPivotBaseToFields';
import { mapMetadataToPivotFields } from '../../src/shared/pivot/utils/mapMetadataToPivotFields';

describe('applyPivotBaseToFields', () => {
  it('asigna filas y metricas desde pivotBase', () => {
    const campos = [
      {
        campoId: 'codCliente',
        dataField: 'codCliente',
        caption: 'Cliente',
        tipoDato: 'string',
        rolCampo: 'dimension',
        rolesPermitidos: ['fila'],
      },
      {
        campoId: 'cantidad',
        dataField: 'cantidad',
        caption: 'Cantidad',
        tipoDato: 'number',
        rolCampo: 'metrica',
        rolesPermitidos: ['valor'],
        agregacionDefault: 'sum',
      },
    ];

    const fields = applyPivotBaseToFields(
      mapMetadataToPivotFields(campos),
      {
        filas: ['codCliente'],
        columnas: [],
        valores: [{ campoId: 'cantidad', agregacion: 'sum' }],
      },
      campos,
    );

    expect(fields.find((field) => field.dataField === 'codCliente')?.area).toBe('row');
    expect(fields.find((field) => field.dataField === 'codCliente')?.areaIndex).toBe(0);
    expect(fields.find((field) => field.dataField === 'cantidad')?.area).toBe('data');
    expect(fields.find((field) => field.dataField === 'cantidad')?.summaryType).toBe('sum');
  });
});
