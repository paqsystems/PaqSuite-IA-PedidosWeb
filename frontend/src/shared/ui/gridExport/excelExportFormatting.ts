export type BooleanExportLabels = {
  trueLabel: string;
  falseLabel: string;
};

export type ExcelFormatContext = {
  locale: string;
  booleanLabels: BooleanExportLabels;
};

const excelDateFormats: Record<string, { date: string; datetime: string }> = {
  es: { date: 'dd/mm/yyyy', datetime: 'dd/mm/yyyy hh:mm' },
  en: { date: 'mm/dd/yyyy', datetime: 'mm/dd/yyyy hh:mm' },
  pt: { date: 'dd/mm/yyyy', datetime: 'dd/mm/yyyy hh:mm' },
  fr: { date: 'dd/mm/yyyy', datetime: 'dd/mm/yyyy hh:mm' },
  it: { date: 'dd/mm/yyyy', datetime: 'dd/mm/yyyy hh:mm' },
};

export const formattedHeaderFill = {
  type: 'pattern' as const,
  pattern: 'solid' as const,
  fgColor: { argb: 'FFD9D9D9' },
};

export function resolveExcelDateNumFmt(dataType: string, locale: string): string {
  const localeKey = locale.split('-')[0];
  const formats = excelDateFormats[localeKey] ?? excelDateFormats.es;

  return dataType === 'datetime' ? formats.datetime : formats.date;
}

export function resolveColumnFormatString(format: string | { type?: string; precision?: number } | undefined): string | undefined {
  if (typeof format === 'string') {
    return format;
  }

  if (format && typeof format === 'object' && typeof format.precision === 'number') {
    return `0.${'0'.repeat(format.precision)}`;
  }

  return undefined;
}

export function resolveDecimalNumFmt(columnFormat: string | undefined): string {
  if (!columnFormat) {
    return '0.00';
  }

  if (columnFormat.includes('%')) {
    const fixedDecimals = columnFormat.match(/\.(0+)/);

    if (fixedDecimals) {
      return `0.${fixedDecimals[1]}%`;
    }

    return '0%';
  }

  const decimalZeros = columnFormat.match(/\.(0+)/);

  if (decimalZeros) {
    return `0.${decimalZeros[1]}`;
  }

  if (/[#0](?!.*\.\d)/.test(columnFormat) && !columnFormat.includes('%')) {
    return '0';
  }

  return '0.00';
}

export function isIntegerColumnFormat(columnFormat: string | undefined): boolean {
  if (!columnFormat) {
    return false;
  }

  return !columnFormat.includes('.') && !columnFormat.includes('%');
}

export function formatBooleanExportValue(value: unknown, labels: BooleanExportLabels): string | null {
  if (typeof value !== 'boolean') {
    return null;
  }

  return value ? labels.trueLabel : labels.falseLabel;
}
