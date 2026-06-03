import { apiRequest } from '../../../shared/http/client';
import type { CabeceraCatalogos, ComprobanteCabecera } from '../types/comprobanteCabecera';
import { ordenarArticulosPorDescripcion, ordenarClientesPorRazonSocial } from '../utils/cargaCatalogos';
import { normalizarPorcIvaAlmacenado } from '../utils/renglonesCarga';
import { mapCabeceraFromApi, mapCabeceraToApi, mapCatalogosFromApi } from '../utils/mapCabeceraApi';

export type ClienteOption = {
  codCliente: string;
  nombre: string;
  razonSocial?: string;
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
  cabecera: ComprobanteCabecera;
  catalogos: CabeceraCatalogos;
  renglones: ComprobanteRenglon[];
};

export type GrabarComprobantePayload = {
  accionGrabacion: 'pedido' | 'presupuesto';
  codPedido?: string | null;
  codPedidoOrigen?: string | null;
  codPresupuestoOrigen?: string | null;
  codComprobanteOrigenCopia?: string | null;
  cabecera: ComprobanteCabecera;
  renglones: ComprobanteRenglon[];
};

export type ParametrosCarga = {
  modificaPrecio: boolean;
  modificaBonArt: boolean;
  modificaBonCli: boolean;
  modificaListaPrec: boolean;
  clienteLeyenda1: boolean;
  clienteLeyenda2: boolean;
  clienteLeyenda3: boolean;
  clienteLeyenda4: boolean;
  clienteLeyenda5: boolean;
  functionalProfile: string;
  codMotivoCierreExitoso: string;
  noEliminaPedido: boolean;
  noModificaPedido: boolean;
  cargaRecurrente: boolean;
};

export type ArticuloOption = {
  codArticulo: string;
  descripcion: string;
  porcIva: number;
  bonificacion: number;
  precio?: number;
  disponibleNeto?: number;
  disponibleNetoBase?: number | null;
};

export type GrabarComprobanteResult = {
  cod_pedido?: string;
  estado?: number;
  nro_visible?: number;
  guidSufijo?: string;
  mailEnviado?: boolean;
};

type ApiClienteRow = {
  codCliente?: string;
  codigo?: string;
  nombre: string;
  razonSocial?: string;
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
    [key: string]: unknown;
  };
  detalle: ApiComprobanteDetalleRow[];
  catalogos?: CabeceraCatalogos;
};

function mapRenglonFromApi(row: ApiComprobanteDetalleRow): ComprobanteRenglon {
  return {
    renglon: row.renglon,
    codArticulo: row.cod_articulo,
    descripcionArticulo: row.descripcion_articulo,
    cantidad: row.cantidad,
    precio: row.precio,
    porcBonif: row.porc_bonif,
    porcIva: normalizarPorcIvaAlmacenado(row.porc_iva),
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

  return ordenarClientesPorRazonSocial(
    rows.map((cliente) => ({
      codCliente: cliente.codCliente ?? cliente.codigo ?? '',
      nombre: cliente.nombre,
      razonSocial: cliente.razonSocial ?? cliente.nombre,
    })),
  );
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
      cabecera: mapCabeceraFromApi(cabecera, cabecera.cod_cliente),
      catalogos: mapCatalogosFromApi(response.resultado.catalogos),
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
    cabecera: mapCabeceraToApi(payload.cabecera),
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
  const resultado = response.resultado;

  return {
    ...resultado,
    clienteLeyenda1: resultado.clienteLeyenda1 ?? true,
    clienteLeyenda2: resultado.clienteLeyenda2 ?? true,
    clienteLeyenda3: resultado.clienteLeyenda3 ?? true,
    clienteLeyenda4: resultado.clienteLeyenda4 ?? true,
    clienteLeyenda5: resultado.clienteLeyenda5 ?? true,
  };
}

export async function fetchCabeceraInicial(codCliente: string): Promise<{
  cabecera: ComprobanteCabecera;
  catalogos: CabeceraCatalogos;
}> {
  const response = await apiRequest<{
    cabecera: Record<string, unknown>;
    catalogos?: CabeceraCatalogos;
  }>(`/clientes/${encodeURIComponent(codCliente)}/cabecera-inicial`);

  return {
    cabecera: mapCabeceraFromApi(response.resultado.cabecera, codCliente),
    catalogos: mapCatalogosFromApi(response.resultado.catalogos),
  };
}

/** Tope de ítems por request en combobox de carga (autocompletar con búsqueda remota). */
export const articulosCargaPageSize = 500;

export async function fetchPreciosArticulosPorLista(
  codigos: string[],
  listaPrecios: number,
): Promise<Array<{ codArticulo: string; precio: number }>> {
  const codigosUnicos = [...new Set(codigos.map((codigo) => codigo.trim()).filter(Boolean))];
  if (codigosUnicos.length === 0 || listaPrecios <= 0) {
    return [];
  }

  const params = new URLSearchParams();
  params.set('codigos', codigosUnicos.join(','));
  params.set('lista_precios', String(listaPrecios));
  params.set('page_size', String(Math.min(1000, codigosUnicos.length)));

  const response = await apiRequest<{ items?: ArticuloOption[] }>(`/articulos?${params.toString()}`);

  return (response.resultado.items ?? []).map((articulo) => ({
    codArticulo: articulo.codArticulo,
    precio: articulo.precio ?? 0,
  }));
}

export async function searchArticulos(
  query = '',
  listaPrecios?: number | null,
  pageSize = articulosCargaPageSize,
): Promise<ArticuloOption[]> {
  const params = new URLSearchParams();
  if (query.trim() !== '') {
    params.set('q', query.trim());
  }
  if (listaPrecios !== null && listaPrecios !== undefined && listaPrecios > 0) {
    params.set('lista_precios', String(listaPrecios));
  }
  params.set('page_size', String(Math.min(1000, Math.max(1, pageSize))));

  const path = `/articulos?${params.toString()}`;
  const response = await apiRequest<{ items?: ArticuloOption[] }>(path);
  const items = response.resultado.items ?? [];

  return ordenarArticulosPorDescripcion(
    items.map((articulo) => ({
      ...articulo,
      porcIva: normalizarPorcIvaAlmacenado(articulo.porcIva),
      disponibleNeto: articulo.disponibleNeto ?? 0,
      disponibleNetoBase: articulo.disponibleNetoBase ?? null,
    })),
  );
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
