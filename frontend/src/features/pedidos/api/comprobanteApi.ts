import { apiRequest } from '../../../shared/http/client';

export type ClienteOption = {
  codCliente: string;
  nombre: string;
};

export type ComprobanteRenglon = {
  renglon: number;
  codArticulo: string;
  descripcionArticulo?: string;
  cantidad: number;
  precio: number;
  porcBonif: number;
  porcIva: number;
};

export type ComprobanteDetalle = {
  codPedido: string;
  codCliente: string;
  estado: number;
  nroVisible: number;
  renglones: ComprobanteRenglon[];
};

export type GrabarComprobantePayload = {
  accionGrabacion: 'pedido' | 'presupuesto';
  codPedido?: string | null;
  codPedidoOrigen?: string | null;
  codPresupuestoOrigen?: string | null;
  codComprobanteOrigenCopia?: string | null;
  codCliente: string | null;
  renglones: ComprobanteRenglon[];
};

export type ParametrosCarga = {
  modificaPrecio: boolean;
  modificaBonArt: boolean;
  modificaBonCli: boolean;
  modificaListaPrec: boolean;
  functionalProfile: string;
  codMotivoCierreExitoso: string;
  noEliminaPedido: boolean;
  noModificaPedido: boolean;
};

export type ArticuloOption = {
  codArticulo: string;
  descripcion: string;
  porcIva: number;
  bonificacion: number;
};

export type GrabarComprobanteResult = {
  cod_pedido?: string;
  nro_visible?: number;
  guidSufijo?: string;
  mailEnviado?: boolean;
};

type ApiClienteRow = {
  codCliente?: string;
  codigo?: string;
  nombre: string;
};

type ApiComprobanteDetalleRow = {
  renglon: number;
  cod_articulo: string;
  descripcion_articulo?: string;
  cantidad: number;
  precio: number;
  porc_bonif: number;
  porc_iva: number;
};

type ApiComprobanteResponse = {
  cabecera: {
    cod_pedido: string;
    cod_cliente: string;
    estado: number;
    nro_visible?: number;
  };
  detalle: ApiComprobanteDetalleRow[];
};

function mapRenglonFromApi(row: ApiComprobanteDetalleRow): ComprobanteRenglon {
  return {
    renglon: row.renglon,
    codArticulo: row.cod_articulo,
    descripcionArticulo: row.descripcion_articulo,
    cantidad: row.cantidad,
    precio: row.precio,
    porcBonif: row.porc_bonif,
    porcIva: row.porc_iva,
  };
}

function mapRenglonToApi(renglon: ComprobanteRenglon) {
  return {
    renglon: renglon.renglon,
    cod_articulo: renglon.codArticulo,
    descripcion_articulo: renglon.descripcionArticulo ?? '',
    cantidad: renglon.cantidad,
    precio: renglon.precio,
    porc_bonif: renglon.porcBonif,
    porc_iva: renglon.porcIva,
  };
}

export async function fetchClientes(): Promise<ClienteOption[]> {
  const response = await apiRequest<ApiClienteRow[] | { items?: ApiClienteRow[] }>('/clientes');
  const payload = response.resultado;
  const rows = Array.isArray(payload) ? payload : (payload.items ?? []);

  return rows.map((cliente) => ({
    codCliente: cliente.codCliente ?? cliente.codigo ?? '',
    nombre: cliente.nombre,
  }));
}

async function fetchComprobanteFromPath(path: string): Promise<ComprobanteDetalle | null> {
  try {
    const response = await apiRequest<ApiComprobanteResponse>(path);
    const { cabecera, detalle } = response.resultado;

    return {
      codPedido: cabecera.cod_pedido,
      codCliente: cabecera.cod_cliente,
      estado: cabecera.estado,
      nroVisible: cabecera.nro_visible ?? 0,
      renglones: detalle.map(mapRenglonFromApi),
    };
  } catch {
    return null;
  }
}

export async function fetchComprobante(comprobanteId: string): Promise<ComprobanteDetalle> {
  const pedido = await fetchComprobanteFromPath(`/pedidos/${encodeURIComponent(comprobanteId)}`);
  if (pedido !== null) {
    return pedido;
  }

  const presupuesto = await fetchComprobanteFromPath(`/presupuestos/${encodeURIComponent(comprobanteId)}`);
  if (presupuesto !== null) {
    return presupuesto;
  }

  throw new Error('comprobante.notFound');
}

export async function grabarComprobante(
  payload: GrabarComprobantePayload,
): Promise<{ resultado: GrabarComprobanteResult }> {
  const body = {
    accionGrabacion: payload.accionGrabacion,
    cod_pedido: payload.codPedido ?? undefined,
    cod_pedido_origen: payload.codPedidoOrigen ?? undefined,
    cod_presupuesto_origen: payload.codPresupuestoOrigen ?? undefined,
    cod_comprobante_origen_copia: payload.codComprobanteOrigenCopia ?? undefined,
    cabecera: {
      cod_cliente: payload.codCliente,
    },
    renglones: payload.renglones.map(mapRenglonToApi),
  };

  return apiRequest<GrabarComprobanteResult>('/comprobantes/grabar', {
    method: 'POST',
    body: JSON.stringify(body),
  });
}

export async function eliminarPedido(codPedido: string): Promise<void> {
  await apiRequest(`/pedidos/${encodeURIComponent(codPedido)}`, {
    method: 'DELETE',
  });
}

export async function fetchParametrosCarga(): Promise<ParametrosCarga> {
  const response = await apiRequest<ParametrosCarga>('/config/parametros-carga');
  return response.resultado;
}

export async function searchArticulos(query = ''): Promise<ArticuloOption[]> {
  const params = new URLSearchParams();
  if (query.trim() !== '') {
    params.set('q', query.trim());
  }
  params.set('page_size', '20');

  const path = `/articulos?${params.toString()}`;
  const response = await apiRequest<{ items?: ArticuloOption[] }>(path);
  return response.resultado.items ?? [];
}

export async function iniciarEdicionPedido(codPedido: string): Promise<void> {
  await apiRequest(`/pedidos/${encodeURIComponent(codPedido)}/edicion/iniciar`, {
    method: 'POST',
    body: JSON.stringify({}),
  });
}

export async function cancelarEdicionPedido(codPedido: string): Promise<void> {
  await apiRequest(`/pedidos/${encodeURIComponent(codPedido)}/edicion/cancelar`, {
    method: 'POST',
    body: JSON.stringify({}),
  });
}
