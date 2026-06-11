import { describe, expect, it } from 'vitest';
import { preparePivotFields } from '../../src/shared/pivot/utils/preparePivotFields';

describe('preparePivotFields', () => {
  it('elimina expanded, normaliza areaIndex y desactiva totales en filas', () => {
    const prepared = preparePivotFields([
      {
        caption: 'Cliente',
        dataField: 'codCliente',
        area: 'row',
        areaIndex: 0,
        expanded: true,
      },
      {
        caption: 'Razón social',
        dataField: 'razonSocial',
        area: 'row',
        areaIndex: 1,
        expanded: true,
      },
      {
        caption: 'Cantidad',
        dataField: 'cantidad',
        area: 'data',
        summaryType: 'sum',
      },
    ]);

    expect(prepared[0].expanded).toBeUndefined();
    expect(prepared[1].expanded).toBeUndefined();
    expect(prepared[0].areaIndex).toBe(0);
    expect(prepared[1].areaIndex).toBe(1);
    expect(prepared[2].areaIndex).toBeUndefined();
  });
});
