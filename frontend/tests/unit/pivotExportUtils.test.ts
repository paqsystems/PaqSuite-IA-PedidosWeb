import { describe, expect, it } from 'vitest';
import { buildPivotExportFileName } from '../../src/features/pivotExport/utils/buildPivotExportFileName';
import { isPivotExportEmpty } from '../../src/features/pivotExport/utils/isPivotExportEmpty';
import {
  hasAnyPivotExportMode,
  resolvePivotExportFlags,
} from '../../src/features/pivotExport/utils/resolvePivotExportFlags';

describe('buildPivotExportFileName', () => {
  it('genera nombre con consultaId y timestamp', () => {
    const exportedAt = new Date('2026-06-11T14:05:00');

    expect(buildPivotExportFileName('CONSULTA_PILOTO_PIVOT', exportedAt)).toBe(
      'CONSULTA_PILOTO_PIVOT_20260611_1405.xlsx',
    );
  });
});

describe('resolvePivotExportFlags', () => {
  it('lee flags canonicos y alias legacy', () => {
    expect(
      resolvePivotExportFlags({
        habilitarExcelBasico: true,
        habilitarExcelTablaDinamica: true,
      }),
    ).toEqual({
      excelBasicoHabilitado: true,
      excelFormateadoHabilitado: true,
      incluirFiltrosAplicados: false,
      incluirMetadatos: false,
    });
  });

  it('detecta si hay alguna modalidad habilitada', () => {
    expect(hasAnyPivotExportMode(resolvePivotExportFlags({}))).toBe(false);
    expect(
      hasAnyPivotExportMode(resolvePivotExportFlags({ excelBasicoHabilitado: true })),
    ).toBe(true);
  });
});

describe('isPivotExportEmpty', () => {
  it('marca vacio sin store o sin metricas en areas', () => {
    expect(isPivotExportEmpty([], null)).toBe(true);
    expect(
      isPivotExportEmpty([{ cantidad: 1 }], {
        fields: () => [{ dataField: 'codCliente', area: 'row' }],
      } as never),
    ).toBe(true);
  });
});
