/**
 * Normaliza fechas de consulta a día de calendario `YYYY-MM-DD`.
 * Toma el prefijo de fecha del string API (ISO o date-only) sin aplicar timezone,
 * para no mostrar el día anterior en zonas UTC-3.
 */
export function toConsultaFechaCalendario(value: string | null | undefined): string {
  if (!value) {
    return '';
  }

  const trimmed = value.trim();
  const ymd = /^(\d{4})-(\d{2})-(\d{2})/.exec(trimmed);
  if (ymd) {
    return `${ymd[1]}-${ymd[2]}-${ymd[3]}`;
  }

  return '';
}

/** Date local a medianoche del día de calendario (para DataGrid / DateBox). */
export function parseLocalCalendarDate(value: string | null | undefined): Date | null {
  const ymd = toConsultaFechaCalendario(value);
  if (!ymd) {
    return null;
  }

  const [year, month, day] = ymd.split('-').map(Number);
  return new Date(year, month - 1, day, 0, 0, 0, 0);
}
