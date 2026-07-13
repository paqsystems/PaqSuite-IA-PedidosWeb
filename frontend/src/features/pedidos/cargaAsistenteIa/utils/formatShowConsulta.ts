import type { CargaAsistenteAction } from '../model/cargaAsistenteTypes';

export type ShowConsultaPayload = {
  kind?: string;
  items?: Array<Record<string, unknown>>;
  total?: number;
  totals?: Record<string, unknown>;
  columns?: string[];
};

export type ShowConsultaViewModel = {
  columns: string[];
  headers: string[];
  rows: string[][];
  truncatedNote: string | null;
  totalsParts: Array<{ column: string; label: string; value: string }>;
};

const columnLabelKeys: Record<string, string> = {
  tipoNro: 'pedidos.carga.asistente.consulta.col.tipoNro',
  fecha: 'pedidos.carga.asistente.consulta.col.fecha',
  vencimiento: 'pedidos.carga.asistente.consulta.col.vencimiento',
  saldo: 'pedidos.carga.asistente.consulta.col.saldo',
  nro: 'pedidos.carga.asistente.consulta.col.nro',
  importe: 'pedidos.carga.asistente.consulta.col.importe',
  descripcionArticulo: 'pedidos.carga.asistente.consulta.col.descripcionArticulo',
  cantidad: 'pedidos.carga.asistente.consulta.col.cantidad',
  precioUnitarioNeto: 'pedidos.carga.asistente.consulta.col.precioUnitarioNeto',
  codArticulo: 'pedidos.carga.asistente.consulta.col.codArticulo',
  descripcion: 'pedidos.carga.asistente.consulta.col.descripcion',
  stock: 'pedidos.carga.asistente.consulta.col.stock',
  comprometido: 'pedidos.carga.asistente.consulta.col.comprometido',
  comprometidoWeb: 'pedidos.carga.asistente.consulta.col.comprometidoWeb',
  disponibleNeto: 'pedidos.carga.asistente.consulta.col.disponibleNeto',
};

const dateOnlyColumns = new Set(['fecha', 'vencimiento']);

export const numericConsultaColumns = new Set([
  'saldo',
  'importe',
  'cantidad',
  'precioUnitarioNeto',
  'stock',
  'comprometido',
  'comprometidoWeb',
  'disponibleNeto',
]);

function formatDateOnly(value: string): string {
  const match = value.match(/^(\d{4}-\d{2}-\d{2})/);
  return match?.[1] ?? value;
}

export function formatCellValue(value: unknown, column?: string): string {
  if (value === null || value === undefined) {
    return '';
  }

  if (typeof value === 'number') {
    return Number.isInteger(value) ? String(value) : value.toFixed(2);
  }

  const asText = String(value);
  if (column && dateOnlyColumns.has(column)) {
    return formatDateOnly(asText);
  }

  return asText;
}

function resolveColumnLabel(
  column: string,
  translate: (key: string, options?: Record<string, unknown>) => string,
): string {
  const key = columnLabelKeys[column];
  return key ? translate(key) : column;
}

export function extractShowConsultaPayload(
  actions: CargaAsistenteAction[] | undefined,
): ShowConsultaPayload | null {
  if (!Array.isArray(actions)) {
    return null;
  }

  const action = actions.find((item) => item.action === 'showConsulta');
  if (!action) {
    return null;
  }

  return (action.payload ?? {}) as ShowConsultaPayload;
}

/**
 * Modelo tabular para render HTML (o texto alineado) del payload `showConsulta`.
 */
export function buildShowConsultaViewModel(
  payload: ShowConsultaPayload,
  translate: (key: string, options?: Record<string, unknown>) => string,
): ShowConsultaViewModel | null {
  const items = Array.isArray(payload.items) ? payload.items : [];
  if (items.length === 0) {
    return null;
  }

  const columns =
    Array.isArray(payload.columns) && payload.columns.length > 0
      ? payload.columns
      : Object.keys(items[0] ?? {});

  if (columns.length === 0) {
    return null;
  }

  const headers = columns.map((column) => resolveColumnLabel(column, translate));
  const rows = items.map((row) =>
    columns.map((column) => formatCellValue(row[column], column)),
  );

  const total = Number(payload.total ?? items.length);
  const truncatedNote =
    total > items.length
      ? translate('pedidos.carga.asistente.consulta.truncated', {
          shown: items.length,
          total,
        })
      : null;

  const totals = payload.totals;
  const totalsParts: ShowConsultaViewModel['totalsParts'] = [];
  if (totals && typeof totals === 'object') {
    for (const [key, value] of Object.entries(totals)) {
      if (value === null || value === undefined) {
        continue;
      }
      totalsParts.push({
        column: key,
        label: resolveColumnLabel(key, translate),
        value: formatCellValue(value, key),
      });
    }
  }

  return { columns, headers, rows, truncatedNote, totalsParts };
}

function padCell(value: string, width: number, numeric: boolean): string {
  return numeric ? value.padStart(width, ' ') : value.padEnd(width, ' ');
}

/**
 * Formato texto con columnas rellenadas (monoespacio) — fallback / tests.
 */
export function formatShowConsulta(
  payload: ShowConsultaPayload,
  translate: (key: string, options?: Record<string, unknown>) => string,
): string {
  const model = buildShowConsultaViewModel(payload, translate);
  if (!model) {
    return '';
  }

  const widths = model.columns.map((_, columnIndex) => {
    const headerWidth = model.headers[columnIndex]?.length ?? 0;
    const cellWidth = model.rows.reduce(
      (max, row) => Math.max(max, row[columnIndex]?.length ?? 0),
      0,
    );
    return Math.max(headerWidth, cellWidth);
  });

  const formatRow = (cells: string[]) =>
    cells
      .map((cell, index) =>
        padCell(cell, widths[index] ?? 0, numericConsultaColumns.has(model.columns[index] ?? '')),
      )
      .join(' | ');

  const lines = [formatRow(model.headers), ...model.rows.map((row) => formatRow(row))];

  if (model.truncatedNote) {
    lines.push(model.truncatedNote);
  }

  if (model.totalsParts.length > 0) {
    const totalsText = model.totalsParts
      .map((part) => `${part.label}: ${part.value}`)
      .join(' · ');
    lines.push(`${translate('pedidos.carga.asistente.consulta.totales')}: ${totalsText}`);
  }

  return lines.join('\n');
}

export function appendShowConsultaToReply(
  replyText: string,
  actions: CargaAsistenteAction[] | undefined,
  translate: (key: string, options?: Record<string, unknown>) => string,
): string {
  const payload = extractShowConsultaPayload(actions);
  if (!payload) {
    return replyText;
  }

  const detail = formatShowConsulta(payload, translate);
  if (detail === '') {
    return replyText;
  }

  return `${replyText}\n${detail}`;
}
