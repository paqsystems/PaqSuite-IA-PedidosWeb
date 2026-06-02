import { apiRequest } from '../../../shared/http/client';

export type IntegracionLogRow = {
  id: string;
  fecha: string;
  tipo: string;
  severidad: string;
  origen: string;
  mensaje: string;
  procesado: boolean;
};

export type IntegracionLogsFilters = {
  fechaDesde?: string | null;
  fechaHasta?: string | null;
  severidad?: string | null;
  tipo?: string | null;
};

type ApiIntegracionLogItem = {
  id_log: number;
  fecha?: string;
  tipo: string;
  severidad: string;
  origen: string;
  mensaje: string;
  procesado: boolean;
};

type ApiIntegracionLogsResponse = {
  items: ApiIntegracionLogItem[];
  metadata?: {
    fecha_proceso?: string;
  };
};

function mapLogItem(item: ApiIntegracionLogItem): IntegracionLogRow {
  return {
    id: String(item.id_log),
    fecha: item.fecha ?? '',
    tipo: item.tipo,
    severidad: item.severidad,
    origen: item.origen,
    mensaje: item.mensaje,
    procesado: item.procesado,
  };
}

export async function fetchIntegracionLogs(
  filters: IntegracionLogsFilters = {},
): Promise<{ items: IntegracionLogRow[]; fechaProceso: string | null }> {
  const params = new URLSearchParams();
  params.set('page_size', '50');

  if (filters.fechaDesde) {
    params.set('fecha_desde', filters.fechaDesde);
  }

  if (filters.fechaHasta) {
    params.set('fecha_hasta', filters.fechaHasta);
  }

  if (filters.severidad) {
    params.set('severidad', filters.severidad);
  }

  if (filters.tipo) {
    params.set('tipo', filters.tipo);
  }

  const response = await apiRequest<ApiIntegracionLogsResponse>(
    `/integracion/logs?${params.toString()}`,
  );

  return {
    items: (response.resultado.items ?? []).map(mapLogItem),
    fechaProceso: response.resultado.metadata?.fecha_proceso ?? null,
  };
}
