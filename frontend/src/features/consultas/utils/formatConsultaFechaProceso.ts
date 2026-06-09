export function formatConsultaFechaProceso(
  isoValue: string | undefined | null,
  locale: string,
): string {
  if (!isoValue) {
    return '';
  }

  const date = new Date(isoValue);

  if (Number.isNaN(date.getTime())) {
    return isoValue;
  }

  const datePart = new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date);

  const timePart = new Intl.DateTimeFormat(locale, {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(date);

  return `${datePart} ${timePart}`;
}
