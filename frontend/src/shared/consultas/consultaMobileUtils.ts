export function formatConsultaAmount(value: number | null | undefined): string {
  if (value === null || value === undefined) {
    return '—';
  }

  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

export function formatConsultaDate(value: string | null | undefined): string {
  if (!value) {
    return '—';
  }

  const ymd = /^(\d{4})-(\d{2})-(\d{2})/.exec(value.trim());
  if (ymd) {
    return `${ymd[3]}/${ymd[2]}/${ymd[1]}`;
  }

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'UTC',
  }).format(parsed);
}

export function matchesConsultaQuery(item: Record<string, unknown>, query: string): boolean {
  const normalizedQuery = query.trim().toLowerCase();
  if (normalizedQuery.length === 0) {
    return true;
  }

  return Object.values(item).some((value) => {
    if (value === null || value === undefined) {
      return false;
    }

    return String(value).toLowerCase().includes(normalizedQuery);
  });
}
