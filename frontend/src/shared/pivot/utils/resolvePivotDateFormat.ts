const pivotDateFormats: Record<string, { date: string; datetime: string }> = {
  es: { date: 'dd/MM/yyyy', datetime: 'dd/MM/yyyy HH:mm' },
  en: { date: 'MM/dd/yyyy', datetime: 'MM/dd/yyyy HH:mm' },
  pt: { date: 'dd/MM/yyyy', datetime: 'dd/MM/yyyy HH:mm' },
  fr: { date: 'dd/MM/yyyy', datetime: 'dd/MM/yyyy HH:mm' },
  it: { date: 'dd/MM/yyyy', datetime: 'dd/MM/yyyy HH:mm' },
};

export function resolvePivotDateFormat(locale: string, includeTime = false): string {
  const localeKey = locale.split('-')[0];
  const formats = pivotDateFormats[localeKey] ?? pivotDateFormats.es;

  return includeTime ? formats.datetime : formats.date;
}
