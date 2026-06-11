import { Workbook, type Worksheet } from 'exceljs';
import type dxDataGrid from 'devextreme/ui/data_grid';
import { exportDataGrid } from 'devextreme/excel_exporter';
import {
  type ExcelFormatContext,
  formatBooleanExportValue,
  formattedHeaderFill,
  isIntegerColumnFormat,
  resolveColumnFormatString,
  resolveDecimalNumFmt,
  resolveExcelDateNumFmt,
} from './excelExportFormatting';
import type { ExportMode } from './model/exportMode';
import { saveExcelWithPicker } from './saveExcelWithPicker';

type GridCellLike = {
  rowType?: string;
  column?: {
    dataField?: string;
    dataType?: string;
    format?: string | { type?: string; precision?: number };
  };
  value?: unknown;
};

type ExcelCellLike = {
  value?: unknown;
  font?: { bold?: boolean };
  numFmt?: string;
  fill?: {
    type: 'pattern';
    pattern: 'solid';
    fgColor: { argb: string };
  };
};

type CustomizeCellArg = {
  gridCell?: GridCellLike;
  excelCell?: ExcelCellLike;
};

function clearExcelCellStyle(excelCell: ExcelCellLike): void {
  delete excelCell.numFmt;
  delete excelCell.fill;

  if (excelCell.font) {
    excelCell.font = { ...excelCell.font, bold: false };
  }
}

function applyFormattedHeaderStyle(excelCell: ExcelCellLike): void {
  excelCell.font = { ...(excelCell.font ?? {}), bold: true };
  excelCell.fill = formattedHeaderFill;
}

function applyFormattedNumberStyle(
  gridCell: GridCellLike,
  excelCell: ExcelCellLike,
): void {
  const columnFormat = resolveColumnFormatString(gridCell.column?.format);
  const numericValue = Number(gridCell.value);

  if (Number.isNaN(numericValue)) {
    return;
  }

  if (Number.isInteger(numericValue) && isIntegerColumnFormat(columnFormat)) {
    excelCell.numFmt = '0';
    return;
  }

  excelCell.numFmt = resolveDecimalNumFmt(columnFormat);
}

function customizeBasicExportCell(cellInfo: CustomizeCellArg): void {
  if (!cellInfo.gridCell || !cellInfo.excelCell) {
    return;
  }

  const { gridCell, excelCell } = cellInfo;

  clearExcelCellStyle(excelCell);

  if (gridCell.rowType !== 'data') {
    return;
  }

  const dataType = gridCell.column?.dataType;

  if (dataType === 'date' || dataType === 'datetime') {
    const value = gridCell.value;

    if (value instanceof Date) {
      excelCell.value = value.toISOString();
    }
  }
}

function customizeFormattedExportCell(
  cellInfo: CustomizeCellArg,
  formatContext: ExcelFormatContext,
): void {
  if (!cellInfo.gridCell || !cellInfo.excelCell) {
    return;
  }

  const { gridCell, excelCell } = cellInfo;

  if (gridCell.rowType === 'header') {
    applyFormattedHeaderStyle(excelCell);
    return;
  }

  if (gridCell.rowType === 'totalFooter' || gridCell.rowType === 'groupFooter') {
    excelCell.font = { ...(excelCell.font ?? {}), bold: true };

    if (gridCell.column?.dataType === 'number') {
      applyFormattedNumberStyle(gridCell, excelCell);
    }

    return;
  }

  if (gridCell.rowType !== 'data') {
    return;
  }

  const dataType = gridCell.column?.dataType;
  const booleanLabel = formatBooleanExportValue(gridCell.value, formatContext.booleanLabels);

  if (booleanLabel !== null || dataType === 'boolean') {
    clearExcelCellStyle(excelCell);
    excelCell.value = booleanLabel ?? formatContext.booleanLabels.falseLabel;
    return;
  }

  if (dataType === 'date' || dataType === 'datetime') {
    const value = gridCell.value;

    if (value instanceof Date) {
      excelCell.value = value;
      excelCell.numFmt = resolveExcelDateNumFmt(dataType, formatContext.locale);
    }

    return;
  }

  if (dataType === 'number') {
    applyFormattedNumberStyle(gridCell, excelCell);
  }
}

function exportVisibleRowsFallback(
  gridInstance: dxDataGrid,
  worksheet: Worksheet,
  mode: ExportMode,
  formatContext: ExcelFormatContext,
): void {
  const columns = gridInstance
    .getVisibleColumns()
    .filter((column) => column.dataField && column.type !== 'buttons');

  const headerRow = columns.map((column) => column.caption ?? column.dataField ?? '');
  worksheet.addRow(headerRow);

  if (mode === 'formatted') {
    const header = worksheet.getRow(1);
    header.font = { bold: true };
    header.fill = formattedHeaderFill;
  }

  gridInstance.getVisibleRows().forEach((row) => {
    if (row.rowType !== 'data' || !row.data) {
      return;
    }

    const values = columns.map((column) => {
      const dataField = column.dataField as string;
      const rawValue = (row.data as Record<string, unknown>)[dataField];

      if (mode === 'formatted' && typeof rawValue === 'boolean') {
        return formatBooleanExportValue(rawValue, formatContext.booleanLabels) ?? '';
      }

      if (mode === 'basic' && rawValue instanceof Date) {
        return rawValue.toISOString();
      }

      return rawValue;
    });

    const addedRow = worksheet.addRow(values);

    if (mode === 'formatted') {
      columns.forEach((column, columnIndex) => {
        const cell = addedRow.getCell(columnIndex + 1);
        const dataField = column.dataField as string;
        const rawValue = (row.data as Record<string, unknown>)[dataField];

        if (column.dataType === 'date' || column.dataType === 'datetime') {
          if (rawValue instanceof Date) {
            cell.value = rawValue;
            cell.numFmt = resolveExcelDateNumFmt(column.dataType, formatContext.locale);
          }
        }

        if (column.dataType === 'number' && typeof rawValue === 'number') {
          const columnFormat = resolveColumnFormatString(column.format as string | undefined);

          if (Number.isInteger(rawValue) && isIntegerColumnFormat(columnFormat)) {
            cell.numFmt = '0';
          } else {
            cell.numFmt = resolveDecimalNumFmt(columnFormat);
          }
        }
      });
    }
  });
}

/**
 * Exporta la vista visible actual del DataGrid (página/filtros/orden/layout activo).
 */
export async function exportDataGridExcel(
  gridInstance: dxDataGrid,
  mode: ExportMode,
  suggestedFileName: string,
  formatContext: ExcelFormatContext,
): Promise<void> {
  const isFormatted = mode === 'formatted';
  let workbook = new Workbook();

  try {
    const worksheet = workbook.addWorksheet('Export');
    await Promise.race([
      exportDataGrid({
        component: gridInstance,
        worksheet,
        autoFilterEnabled: isFormatted,
        customizeCell: (cellInfo) => {
          if (isFormatted) {
            customizeFormattedExportCell(cellInfo as CustomizeCellArg, formatContext);
            return;
          }

          customizeBasicExportCell(cellInfo as CustomizeCellArg);
        },
      }),
      new Promise((_, reject) => {
        window.setTimeout(() => reject(new Error('exportDataGrid timeout')), 8_000);
      }),
    ]);
  } catch {
    workbook = new Workbook();
    const worksheet = workbook.addWorksheet('Export');
    exportVisibleRowsFallback(gridInstance, worksheet, mode, formatContext);
  }

  const buffer = (await workbook.xlsx.writeBuffer()) as ArrayBuffer;
  await saveExcelWithPicker(buffer, suggestedFileName);
}
