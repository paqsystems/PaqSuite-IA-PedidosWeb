function pad2(value: number): string {
  return String(value).padStart(2, '0');
}

function sanitizeSegment(value: string): string {
  const trimmed = value.trim();

  if (trimmed.length === 0) {
    return 'pivot_export';
  }

  return trimmed.replace(/[^a-zA-Z0-9_-]+/g, '_').replace(/^_+|_+$/g, '');
}

/**
 * Nombre sugerido: `{consultaId}_{yyyyMMdd_HHmm}.xlsx` (AC-10 / RN-06).
 */
export function buildPivotExportFileName(consultaId: string, exportedAt: Date = new Date()): string {
  const dateStamp = `${exportedAt.getFullYear()}${pad2(exportedAt.getMonth() + 1)}${pad2(exportedAt.getDate())}_${pad2(exportedAt.getHours())}${pad2(exportedAt.getMinutes())}`;

  return `${sanitizeSegment(consultaId)}_${dateStamp}.xlsx`;
}
