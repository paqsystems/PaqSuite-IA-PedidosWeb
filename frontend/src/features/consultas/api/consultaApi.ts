import { apiRequest } from '../../../shared/http/client';

export type ConsultaMeta = {
  fecha_proceso?: string;
};

export type ConsultaPayload<T> =
  | T[]
  | {
      items?: T[];
      data?: T[];
      meta?: ConsultaMeta;
    };

export type ConsultaResult<T> = {
  items: T[];
  meta: ConsultaMeta | null;
};

export type PedidoConsultaRow = {
  id: string;
  numero: string;
  cliente: string;
  estado: number;
  importe: number;
};

export type PresupuestoConsultaRow = PedidoConsultaRow;

export type StockConsultaRow = {
  id: string;
  articulo: string;
  stockActual: number;
  stockComprometido: number;
};

export type DeudaConsultaRow = {
  id: string;
  cliente: string;
  vencimiento: string;
  importe: number;
};

export type ChequeConsultaRow = {
  id: string;
  cliente: string;
  banco: string;
  vencimiento: string;
  importe: number;
};

export type HistorialVentasRow = {
  id: string;
  fecha: string;
  cliente: string;
  comprobante: string;
  importe: number;
};

export type HistorialVentaDetalleRow = {
  id: string;
  articulo: string;
  cantidad: number;
  importe: number;
};

async function fetchConsulta<T>(path: string): Promise<ConsultaResult<T>> {
  const response = await apiRequest<ConsultaPayload<T>>(path);
  const payload = response.resultado;

  if (Array.isArray(payload)) {
    return {
      items: payload,
      meta: null,
    };
  }

  return {
    items: payload.items ?? payload.data ?? [],
    meta: payload.meta ?? null,
  };
}

export function fetchPedidosIngresados() {
  return fetchConsulta<PedidoConsultaRow>('/consultas/pedidos-ingresados');
}

export function fetchPedidosPendientes() {
  return fetchConsulta<PedidoConsultaRow>('/consultas/pedidos-pendientes');
}

export function fetchPresupuestosActivos() {
  return fetchConsulta<PresupuestoConsultaRow>('/consultas/presupuestos/activos');
}

export function fetchPresupuestosCerrados() {
  return fetchConsulta<PresupuestoConsultaRow>('/consultas/presupuestos/cerrados');
}

export function fetchStock() {
  return fetchConsulta<StockConsultaRow>('/consultas/stock');
}

export function fetchDeuda() {
  return fetchConsulta<DeudaConsultaRow>('/consultas/deuda');
}

export function fetchCheques() {
  return fetchConsulta<ChequeConsultaRow>('/consultas/cheques');
}

export function fetchHistorialVentas() {
  return fetchConsulta<HistorialVentasRow>('/consultas/historial-ventas');
}

export async function fetchHistorialVentasDetalle(historialId: string) {
  const response = await apiRequest<HistorialVentaDetalleRow[]>(
    `/consultas/historial-ventas/${encodeURIComponent(historialId)}/detalle`,
  );
  return response.resultado;
}
