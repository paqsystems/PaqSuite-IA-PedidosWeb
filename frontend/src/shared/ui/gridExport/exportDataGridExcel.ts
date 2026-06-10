import { Workbook, type Worksheet } from 'exceljs';
import type dxDataGrid from 'devextreme/ui/data_grid';
import { exportDataGrid } from 'devextreme/excel_exporter';
import type { ExportMode } from './model/exportMode';
import { saveExcelWithPicker } from './saveExcelWithPicker';

type GridCellLike = {
  rowType?: string;
  column?: {
    dataField?: string;
    dataType?: string;
  };
  value?: unknown;
};

type ExcelCellLike = {
  value?: unknown;
  font?: { bold?: boolean };
  numFmt?: string;
};

type CustomizeCellArg = {
  gridCell?: GridCellLike;
  excelCell?: ExcelCellLike;
};

function customizeExportCell(cellInfo: CustomizeCellArg, mode: ExportMode): void {
  if (mode === 'basic' || !cellInfo.gridCell || !cellInfo.excelCell) {
    return;
  }

  const { gridCell, excelCell } = cellInfo;

  if (gridCell.rowType === 'header') {
    excelCell.font = { ...(excelCell.font ?? {}), bold: true };
    return;
  }

  if (gridCell.rowType !== 'data') {
    return;
  }

  const dataType = gridCell.column?.dataType;

  if (dataType === 'date' || dataType === 'datetime') {
    const value = gridCell.value;

    if (value instanceof Date) {
      excelCell.value = value;
      excelCell.numFmt = dataType === 'datetime' ? 'dd/mm/yyyy hh:mm' : 'dd/mm/yyyy';
    }
  }

  if (dataType === 'number') {
    const numericValue = Number(gridCell.value);

    if (Number.isInteger(numericValue)) {
      excelCell.numFmt = '0';
    }
  }
}

function exportVisibleRowsFallback(
  gridInstance: dxDataGrid,
  worksheet: Worksheet,
  mode: ExportMode,
): void {
  const columns = gridInstance
    .getVisibleColumns()
    .filter((column) => column.dataField && column.type !== 'buttons');

  const headerRow = columns.map((column) => column.caption ?? column.dataField ?? '');
  worksheet.addRow(headerRow);

  if (mode === 'formatted') {
    worksheet.getRow(1).font = { bold: true };
  }

  gridInstance.getVisibleRows().forEach((row) => {
    if (row.rowType !== 'data' || !row.data) {
      return;
    }

    const values = columns.map((column) => {
      const dataField = column.dataField as string;
      return (row.data as Record<string, unknown>)[dataField];
    });
    worksheet.addRow(values);
  });
}

/**
 * Exporta la vista visible actual del DataGrid (página/filtros/orden/layout activo).
 */
export async function exportDataGridExcel(
  gridInstance: dxDataGrid,
  mode: ExportMode,
  suggestedFileName: string,
): Promise<void> {
  let workbook = new Workbook();

  try {
    const worksheet = workbook.addWorksheet('Export');
    await Promise.race([
      exportDataGrid({
        component: gridInstance,
        worksheet,
        autoFilterEnabled: true,
        customizeCell: (cellInfo) => customizeExportCell(cellInfo as CustomizeCellArg, mode),
      }),
      new Promise((_, reject) => {
        window.setTimeout(() => reject(new Error('exportDataGrid timeout')), 8_000);
      }),
    ]);
  } catch {
    workbook = new Workbook();
    const worksheet = workbook.addWorksheet('Export');
    exportVisibleRowsFallback(gridInstance, worksheet, mode);
  }

  const buffer = (await workbook.xlsx.writeBuffer()) as ArrayBuffer;
  await saveExcelWithPicker(buffer, suggestedFileName);
}
