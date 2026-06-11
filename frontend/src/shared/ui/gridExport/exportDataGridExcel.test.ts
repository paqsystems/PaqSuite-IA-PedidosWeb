import { describe, expect, it } from 'vitest';

type GridCellLike = {
  rowType?: string;
  column?: { dataType?: string; format?: string };
  value?: unknown;
};

type ExcelCellLike = {
  value?: unknown;
  font?: { bold?: boolean };
  numFmt?: string;
  fill?: unknown;
};

function clearExcelCellStyle(excelCell: ExcelCellLike): void {
  delete excelCell.numFmt;
  delete excelCell.fill;

  if (excelCell.font) {
    excelCell.font = { ...excelCell.font, bold: false };
  }
}

function simulateBasicCustomizer(gridCell: GridCellLike, excelCell: ExcelCellLike): void {
  clearExcelCellStyle(excelCell);

  if (gridCell.rowType !== 'data') {
    return;
  }

  if (gridCell.column?.dataType === 'date' && gridCell.value instanceof Date) {
    excelCell.value = gridCell.value.toISOString();
  }
}

function simulateFormattedHeader(excelCell: ExcelCellLike): void {
  excelCell.font = { bold: true };
  excelCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9D9D9' } };
}

describe('exportDataGridExcel modes', () => {
  it('basica limpia numFmt y negrita aplicados por DevExtreme', () => {
    const excelCell: ExcelCellLike = {
      value: 1500.5,
      numFmt: '#,##0.00',
      font: { bold: true },
    };

    simulateBasicCustomizer({ rowType: 'data', column: { dataType: 'number' }, value: 1500.5 }, excelCell);

    expect(excelCell.numFmt).toBeUndefined();
    expect(excelCell.font?.bold).toBe(false);
    expect(excelCell.value).toBe(1500.5);
  });

  it('formateada resalta encabezados con fondo gris', () => {
    const excelCell: ExcelCellLike = { value: 'Cliente' };

    simulateFormattedHeader(excelCell);

    expect(excelCell.font?.bold).toBe(true);
    expect(excelCell.fill).toBeDefined();
  });
});
