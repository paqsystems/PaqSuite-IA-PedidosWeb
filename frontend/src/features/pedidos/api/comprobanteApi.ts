import { apiRequest } from '../../../shared/http/client';

export type ClienteOption = {
  codigo: string;
  nombre: string;
};

export type ComprobanteRenglon = {
  id: string;
  articulo: string;
  cantidad: number;
  precio: number;
  subtotal: number;
};

export type Comprobante = {
  id: string;
  codCliente: string | null;
  renglones: ComprobanteRenglon[];
};

export type GrabarComprobantePayload = {
  accionGrabacion: 'grabar_pedido' | 'grabar_presupuesto';
  codComprobanteOrigen?: string | null;
  codCliente: string | null;
  renglones: ComprobanteRenglon[];
};

export type GrabarComprobanteResult = {
  codComprobante?: string;
  numeroVisible?: number;
  guidSufijo?: string;
  mailEnviado?: boolean;
};

type ApiEnvelope<T> = {
  error: number;
  respuesta: string;
  resultado: T;
};

export async function fetchClientes(): Promise<ClienteOption[]> {
  const response = await apiRequest<ClienteOption[] | { items?: ClienteOption[] }>('/clientes');
  const payload = response.resultado;

  if (Array.isArray(payload)) {
    return payload;
  }

  return payload.items ?? [];
}

export async function fetchComprobante(comprobanteId: string): Promise<Comprobante> {
  const response = await apiRequest<Comprobante>(`/comprobantes/${encodeURIComponent(comprobanteId)}`);
  return response.resultado;
}

export async function grabarComprobante(
  payload: GrabarComprobantePayload,
): Promise<ApiEnvelope<GrabarComprobanteResult>> {
  return apiRequest<GrabarComprobanteResult>('/comprobantes/grabar', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}
