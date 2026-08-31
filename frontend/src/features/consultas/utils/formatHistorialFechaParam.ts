/**
 * Serializa una fecha de calendario (sin hora) a `YYYY-MM-DD` para query params.
 * Evita el off-by-one típico: DateBox `type="date"` guarda medianoche UTC y
 * `getDate()` local en UTC-3 cae al día anterior.
 */
export function formatHistorialFechaParam(
  value: Date | string | number | null | undefined,
): string | undefined {
  if (value === null || value === undefined || value === '') {
    return undefined;
  }

  if (typeof value === 'string') {
    const trimmed = value.trim();
    if (/^\d{4}-\d{2}-\d{2}/.test(trimmed)) {
      return trimmed.slice(0, 10);
    }

    const dmy = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(trimmed);
    if (dmy) {
      return `${dmy[3]}-${dmy[2]}-${dmy[1]}`;
    }

    const parsed = new Date(trimmed);
    if (Number.isNaN(parsed.getTime())) {
      return undefined;
    }

    return formatDateObjectAsCalendarDay(parsed);
  }

  if (typeof value === 'number') {
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
      return undefined;
    }

    return formatDateObjectAsCalendarDay(parsed);
  }

  if (value instanceof Date) {
    if (Number.isNaN(value.getTime())) {
      return undefined;
    }

    return formatDateObjectAsCalendarDay(value);
  }

  return undefined;
}

function formatDateObjectAsCalendarDay(date: Date): string {
  const isUtcMidnight =
    date.getUTCHours() === 0 &&
    date.getUTCMinutes() === 0 &&
    date.getUTCSeconds() === 0 &&
    date.getUTCMilliseconds() === 0;

  const year = isUtcMidnight ? date.getUTCFullYear() : date.getFullYear();
  const month = (isUtcMidnight ? date.getUTCMonth() : date.getMonth()) + 1;
  const day = isUtcMidnight ? date.getUTCDate() : date.getDate();

  return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}
