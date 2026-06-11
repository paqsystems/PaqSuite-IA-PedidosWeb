import { Workbook } from 'exceljs';
import type dxPivotGrid from 'devextreme/ui/pivot_grid';
import { exportPivotGrid } from 'devextreme/excel_exporter';
import { saveExcelWithPicker } from '../../../shared/ui/gridExport/saveExcelWithPicker';
import type { PivotExportFlags } from './resolvePivotExportFlags';

export type PivotExportMode = 'basic' | 'pivotTable';

export type PivotExportContext = {
  consultaId: string;
  userDisplayName?: string | null;
  activeLayoutName?: string | null;
  appliedFilters?: Record<string, unknown>;
  exportacion: PivotExportFlags;
  exportedAt: Date;
  datasetTruncado?: boolean;
  labels: {
    metaConsulta: string;
    metaUsuario: string;
    metaDiseno: string;
    metaFecha: string;
    metaFiltros: string;
    metaTruncado: string;
    initialTemplate: string;
    metaYes: string;
    metaNo: string;
  };
};

function formatFilterValue(value: unknown): string {
  if (value === null || value === undefined) {
    return '';
  }

  if (value instanceof Date) {
    return value.toISOString();
  }

  return String(value);
}

function appendMetadataSheet(workbook: Workbook, context: PivotExportContext): void {
  const worksheet = workbook.addWorksheet('_meta');
  const rows: Array<[string, string]> = [
    [context.labels.metaConsulta, context.consultaId],
    [context.labels.metaUsuario, context.userDisplayName ?? ''],
    [
      context.labels.metaDiseno,
      context.activeLayoutName?.trim() || context.labels.initialTemplate,
    ],
    [context.labels.metaFecha, context.exportedAt.toISOString()],
  ];

  if (context.exportacion.incluirFiltrosAplicados) {
    const filtros = context.appliedFilters ?? {};
    const filtrosTexto = Object.entries(filtros)
      .map(([field, value]) => `${field}=${formatFilterValue(value)}`)
      .join('; ');

    rows.push([context.labels.metaFiltros, filtrosTexto]);
  }

  if (context.datasetTruncado) {
    rows.push([context.labels.metaTruncado, context.labels.metaYes]);
  }

  rows.forEach(([label, value]) => {
    worksheet.addRow([label, value]);
  });

  worksheet.getColumn(1).width = 28;
  worksheet.getColumn(2).width = 48;
}

export async function exportPivotGridExcel(
  pivotInstance: dxPivotGrid,
  mode: PivotExportMode,
  suggestedFileName: string,
  context: PivotExportContext,
): Promise<{ pivotTableLimited: boolean }> {
  const workbook = new Workbook();
  const worksheet = workbook.addWorksheet('Export');
  let pivotTableLimited = false;

  try {
    await Promise.race([
      exportPivotGrid({
        component: pivotInstance,
        worksheet,
        mergeRowFieldValues: mode === 'pivotTable',
      }),
      new Promise((_, reject) => {
        window.setTimeout(() => reject(new Error('exportPivotGrid timeout')), 8_000);
      }),
    ]);
  } catch {
    pivotTableLimited = mode === 'pivotTable';
    worksheet.spliceRows(1, worksheet.rowCount);
    await exportPivotGrid({
      component: pivotInstance,
      worksheet,
      mergeRowFieldValues: false,
    });
  }

  if (context.exportacion.incluirMetadatos) {
    appendMetadataSheet(workbook, context);
  }

  const buffer = (await workbook.xlsx.writeBuffer()) as ArrayBuffer;
  await saveExcelWithPicker(buffer, suggestedFileName);

  return { pivotTableLimited };
}
