function pad2(value: number): string {
  return String(value).padStart(2, '0');
}

function sanitizeSegment(value: string): string {
  const trimmed = value.trim();

  if (trimmed.length === 0) {
    return 'export';
  }

  return trimmed.replace(/[^a-zA-Z0-9_-]+/g, '_').replace(/^_+|_+$/g, '');
}

/**
 * Nombre sugerido: `{proceso}_{gridId?}_{yyyyMMdd_HHmm}.xlsx` (RN-06 / TR-GEN-03-exportaciones).
 * Alcance export: página actual visible del DataGrid (sin dataset completo).
 */
export function buildSuggestedExportFileName(proceso: string, gridId?: string): string {
  const now = new Date();
  const dateStamp = `${now.getFullYear()}${pad2(now.getMonth() + 1)}${pad2(now.getDate())}_${pad2(now.getHours())}${pad2(now.getMinutes())}`;
  const procesoSegment = sanitizeSegment(proceso);
  const gridSegment = gridId ? `_${sanitizeSegment(gridId)}` : '';

  return `${procesoSegment}${gridSegment}_${dateStamp}.xlsx`;
}
