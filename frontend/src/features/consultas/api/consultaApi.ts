import { apiRequest } from '../../../shared/http/client';

export type ConsultaMeta = {
  fecha_proceso?: string;
  dias_ventas_detalladas?: number;
};

export type ConsultaPayload<T> = {
  items?: T[];
  metadata?: ConsultaMeta;
  page?: number;
  page_size?: number;
  total?: number;
  total_pages?: number;
};

export type ConsultaResult<T> = {
  items: T[];
  meta: ConsultaMeta | null;
};

export type PresupuestoCierreInfo = {
  tipoCierre: string;
  motivoDescripcion: string;
  fechaCierre: string;
  codPedidoGenerado?: string | null;
  observacion?: string;
};

export type ComprobanteConsultaRow = {
  id: string;
  codPedido: string;
  numero: string;
  cliente: string;
  codCliente: string;
  razonSocial: string;
  nombreFantasia: string;
  estado: number;
  importe: number;
  fecha: string;
  nivel: number | null;
  observaciones: string;
  incluyeIva: boolean;
  moneda: number;
  fechaModif: string;
  total: number;
  totalIva: number;
  leyenda1: string;
  leyenda2: string;
  leyenda3: string;
  leyenda4: string;
  leyenda5: string;
  descuento: number;
  bonif1: number;
  bonif2: number;
  bonif3: number;
  codPerfil: string;
  perfilDescripcion: string;
  codVended: string;
  vendedorDescripcion: string;
  codCondvta: number | null;
  condicionVentaDescripcion: string;
  idDe: number | null;
  direccionEntregaDescripcion: string;
  codTranspor: string;
  transporteDescripcion: string;
  listaPrecios: number | null;
  listaPreciosDescripcion: string;
  expreso: string;
  expresoDire: string;
  fechaEntrega: string;
  usuarioCreacion: string;
  fechaCreacion: string;
  usuarioModificacion: string;
  fechahoraInicioProceso: string;
  fechahoraUltimaActividad: string;
  puedeEditar: boolean;
  puedeEliminar: boolean;
  puedeCopiar: boolean;
  puedeConvertir: boolean;
  puedeCerrar: boolean;
  cierre?: PresupuestoCierreInfo;
};

export type PedidoConsultaRow = ComprobanteConsultaRow;

export type PresupuestoConsultaRow = ComprobanteConsultaRow;

export type DetallePedidoConsultaRow = ComprobanteConsultaRow & {
  renglon: number;
  codArticulo: string;
  descripcionArticulo: string;
  cantidad: number;
  porcBonif: number;
  precioLista: number;
  precioNeto: number;
  importeBruto: number;
  importeNeto: number;
  ivaNeto: number;
  importeNetoConIva: number;
};

export type StockConsultaRow = {
  id: string;
  codArticulo: string;
  descripcion: string;
  stock: number;
  comprometido: number;
  comprometidoWeb: number;
  disponibleNeto: number;
  codBase: string | null;
  stockBase: number | null;
  comprometidoBase: number | null;
  comprometidoBaseWeb: number | null;
  disponibleNetoBase: number | null;
};

export type DeudaConsultaRow = {
  id: string;
  codCliente: string;
  razonSocial: string;
  tipo: string;
  numero: string;
  fecha: string;
  vencimiento: string;
  saldo: number;
};

export type ChequeConsultaRow = {
  id: string;
  interno: string;
  numero: string;
  codCliente: string;
  nombreCliente: string;
  banco: string;
  fecha: string;
  importe: number;
  origen: string;
  estado: string;
};

export type HistorialVentasRow = {
  id: string;
  codCliente: string;
  razonSocial: string;
  nRemito: string;
  tipo: string;
  numero: string;
  fechaEmision: string;
  condVta: number | null;
  porcDesc: number;
  cotiz: number;
  moneda: string;
  totalComp: number;
  codTransp: string;
  nomTransp: string;
  codArticulo: string;
  descripcion: string;
  codDep: string;
  um: string;
  cantidad: number;
  precio: number;
  totSinImp: number;
  nCompRem: string;
  cantRem: number;
  fechaRem: string;
};

type ApiComprobanteConsultaItem = {
  codPedido?: string;
  codPresupuesto?: string;
  codCliente?: string;
  razonSocial?: string;
  nombreFantasia?: string;
  estado?: number;
  fecha?: string;
  nivel?: number | null;
  observaciones?: string;
  incluyeIva?: boolean;
  moneda?: number;
  fechaModif?: string;
  total?: number;
  totalIva?: number;
  leyenda1?: string;
  leyenda2?: string;
  leyenda3?: string;
  leyenda4?: string;
  leyenda5?: string;
  descuento?: number;
  bonif1?: number;
  bonif2?: number;
  bonif3?: number;
  codPerfil?: string;
  perfilDescripcion?: string;
  codVended?: string;
  vendedorDescripcion?: string;
  codCondvta?: number | null;
  condicionVentaDescripcion?: string;
  idDe?: number | null;
  direccionEntregaDescripcion?: string;
  codTranspor?: string;
  transporteDescripcion?: string;
  listaPrecios?: number | null;
  listaPreciosDescripcion?: string;
  expreso?: string;
  expresoDire?: string;
  fechaEntrega?: string;
  usuarioCreacion?: string;
  fechaCreacion?: string;
  usuarioModificacion?: string;
  fechahoraInicioProceso?: string;
  fechahoraUltimaActividad?: string;
  numeroVisible?: number;
  puedeEditar?: boolean;
  puedeEliminar?: boolean;
  puedeCopiar?: boolean;
  puedeConvertir?: boolean;
  puedeCerrar?: boolean;
  cierre?: {
    tipoCierre?: string;
    motivoDescripcion?: string;
    fechaCierre?: string;
    codPedidoGenerado?: string | null;
    observacion?: string;
  };
};

type ApiDetallePedidoItem = ApiComprobanteConsultaItem & {
  renglon?: number;
  codArticulo?: string;
  descripcionArticulo?: string;
  cantidad?: number;
  porcBonif?: number;
  precioLista?: number;
  precioNeto?: number;
  importeBruto?: number;
  importeNeto?: number;
  ivaNeto?: number;
  importeNetoConIva?: number;
};

type ApiStockItem = {
  codArticulo?: string;
  descripcion?: string;
  stock?: number;
  comprometido?: number;
  comprometidoWeb?: number;
  disponibleNeto?: number;
  codBase?: string | null;
  stockBase?: number | null;
  comprometidoBase?: number | null;
  comprometidoBaseWeb?: number | null;
  disponibleNetoBase?: number | null;
};

type ApiDeudaItem = {
  codCliente?: string;
  razonSocial?: string;
  tipo?: string;
  numero?: string;
  fecha?: string;
  vencimiento?: string;
  saldo?: number;
};

type ApiChequeItem = {
  interno?: string;
  numero?: string;
  codCliente?: string;
  nombreCliente?: string;
  banco?: string;
  fecha?: string;
  importe?: number;
  origen?: string;
  estado?: string;
};

type ApiHistorialItem = {
  codCliente?: string;
  razonSocial?: string;
  nRemito?: string;
  tipo?: string;
  numero?: string;
  fechaEmision?: string;
  condVta?: number | null;
  porcDesc?: number;
  cotiz?: number;
  moneda?: string;
  totalComp?: number;
  codTransp?: string;
  nomTransp?: string;
  codArticulo?: string;
  descripcion?: string;
  codDep?: string;
  um?: string;
  cantidad?: number;
  precio?: number;
  totSinImp?: number;
  nCompRem?: string;
  cantRem?: number;
  fechaRem?: string;
};

function extractMeta(payload: ConsultaPayload<unknown>): ConsultaMeta | null {
  return payload.metadata ?? null;
}

function mapComprobanteConsultaItem(item: ApiComprobanteConsultaItem): ComprobanteConsultaRow {
  const codPedido = item.codPedido ?? item.codPresupuesto ?? '';
  const numeroVisible = item.numeroVisible ?? 0;
  const razonSocial = item.razonSocial ?? item.codCliente ?? '';

  return {
    id: codPedido,
    codPedido,
    numero: numeroVisible > 0 ? String(numeroVisible) : codPedido,
    cliente: razonSocial,
    codCliente: item.codCliente ?? '',
    razonSocial,
    nombreFantasia: item.nombreFantasia ?? '',
    estado: item.estado ?? 0,
    importe: item.total ?? 0,
    fecha: item.fecha ?? '',
    nivel: item.nivel ?? null,
    observaciones: item.observaciones ?? '',
    incluyeIva: item.incluyeIva ?? false,
    moneda: item.moneda ?? 1,
    fechaModif: item.fechaModif ?? '',
    total: item.total ?? 0,
    totalIva: item.totalIva ?? 0,
    leyenda1: item.leyenda1 ?? '',
    leyenda2: item.leyenda2 ?? '',
    leyenda3: item.leyenda3 ?? '',
    leyenda4: item.leyenda4 ?? '',
    leyenda5: item.leyenda5 ?? '',
    descuento: item.descuento ?? 0,
    bonif1: item.bonif1 ?? 0,
    bonif2: item.bonif2 ?? 0,
    bonif3: item.bonif3 ?? 0,
    codPerfil: item.codPerfil ?? '',
    perfilDescripcion: item.perfilDescripcion ?? '',
    codVended: item.codVended ?? '',
    vendedorDescripcion: item.vendedorDescripcion ?? '',
    codCondvta: item.codCondvta ?? null,
    condicionVentaDescripcion: item.condicionVentaDescripcion ?? '',
    idDe: item.idDe ?? null,
    direccionEntregaDescripcion: item.direccionEntregaDescripcion ?? '',
    codTranspor: item.codTranspor ?? '',
    transporteDescripcion: item.transporteDescripcion ?? '',
    listaPrecios: item.listaPrecios ?? null,
    listaPreciosDescripcion: item.listaPreciosDescripcion ?? '',
    expreso: item.expreso ?? '',
    expresoDire: item.expresoDire ?? '',
    fechaEntrega: item.fechaEntrega ?? '',
    usuarioCreacion: item.usuarioCreacion ?? '',
    fechaCreacion: item.fechaCreacion ?? '',
    usuarioModificacion: item.usuarioModificacion ?? '',
    fechahoraInicioProceso: item.fechahoraInicioProceso ?? '',
    fechahoraUltimaActividad: item.fechahoraUltimaActividad ?? '',
    puedeEditar: item.puedeEditar ?? false,
    puedeEliminar: item.puedeEliminar ?? false,
    puedeCopiar: item.puedeCopiar ?? false,
    puedeConvertir: item.puedeConvertir ?? false,
    puedeCerrar: item.puedeCerrar ?? false,
    cierre: item.cierre
      ? {
          tipoCierre: item.cierre.tipoCierre ?? '',
          motivoDescripcion: item.cierre.motivoDescripcion ?? '',
          fechaCierre: item.cierre.fechaCierre ?? '',
          codPedidoGenerado: item.cierre.codPedidoGenerado,
          observacion: item.cierre.observacion,
        }
      : undefined,
  };
}

function mapDetallePedidoItem(item: ApiDetallePedidoItem): DetallePedidoConsultaRow {
  const base = mapComprobanteConsultaItem(item);
  const codPedido = item.codPedido ?? base.codPedido;
  const renglon = item.renglon ?? 0;

  return {
    ...base,
    id: `${codPedido}-${renglon}`,
    renglon,
    codArticulo: item.codArticulo ?? '',
    descripcionArticulo: item.descripcionArticulo ?? '',
    cantidad: item.cantidad ?? 0,
    porcBonif: item.porcBonif ?? 0,
    precioLista: item.precioLista ?? 0,
    precioNeto: item.precioNeto ?? 0,
    importeBruto: item.importeBruto ?? 0,
    importeNeto: item.importeNeto ?? 0,
    ivaNeto: item.ivaNeto ?? 0,
    importeNetoConIva: item.importeNetoConIva ?? 0,
  };
}

function mapStockItem(item: ApiStockItem, index: number): StockConsultaRow {
  const codArticulo = item.codArticulo ?? `stock-${index}`;

  return {
    id: codArticulo,
    codArticulo,
    descripcion: item.descripcion ?? '',
    stock: item.stock ?? 0,
    comprometido: item.comprometido ?? 0,
    comprometidoWeb: item.comprometidoWeb ?? 0,
    disponibleNeto: item.disponibleNeto ?? 0,
    codBase: item.codBase ?? null,
    stockBase: item.stockBase ?? null,
    comprometidoBase: item.comprometidoBase ?? null,
    comprometidoBaseWeb: item.comprometidoBaseWeb ?? null,
    disponibleNetoBase: item.disponibleNetoBase ?? null,
  };
}

function mapDeudaItem(item: ApiDeudaItem, index: number): DeudaConsultaRow {
  const codCliente = item.codCliente ?? `deuda-${index}`;
  const tipo = item.tipo ?? '';
  const numero = item.numero ?? '';

  return {
    id: `${codCliente}-${tipo}-${numero}-${item.vencimiento ?? index}`,
    codCliente,
    razonSocial: item.razonSocial ?? '',
    tipo,
    numero,
    fecha: item.fecha ?? '',
    vencimiento: item.vencimiento ?? '',
    saldo: item.saldo ?? 0,
  };
}

function mapChequeItem(item: ApiChequeItem, index: number): ChequeConsultaRow {
  const interno = item.interno ?? '';
  const numero = item.numero ?? '';
  const codCliente = item.codCliente ?? '';

  return {
    id: `${interno}-${numero}-${codCliente || index}`,
    interno,
    numero,
    codCliente,
    nombreCliente: item.nombreCliente ?? '',
    banco: item.banco ?? '',
    fecha: item.fecha ?? '',
    importe: item.importe ?? 0,
    origen: item.origen ?? '',
    estado: item.estado ?? '',
  };
}

function mapHistorialItem(item: ApiHistorialItem, index: number): HistorialVentasRow {
  const codCliente = item.codCliente ?? '';
  const tipo = item.tipo ?? '';
  const numero = item.numero ?? '';
  const codArticulo = item.codArticulo ?? '';

  return {
    id: `${codCliente}-${tipo}-${numero}-${codArticulo}-${item.fechaEmision ?? index}`,
    codCliente,
    razonSocial: item.razonSocial ?? '',
    nRemito: item.nRemito ?? '',
    tipo,
    numero,
    fechaEmision: item.fechaEmision ?? '',
    condVta: item.condVta ?? null,
    porcDesc: item.porcDesc ?? 0,
    cotiz: item.cotiz ?? 0,
    moneda: item.moneda ?? '',
    totalComp: item.totalComp ?? 0,
    codTransp: item.codTransp ?? '',
    nomTransp: item.nomTransp ?? '',
    codArticulo,
    descripcion: item.descripcion ?? '',
    codDep: item.codDep ?? '',
    um: item.um ?? '',
    cantidad: item.cantidad ?? 0,
    precio: item.precio ?? 0,
    totSinImp: item.totSinImp ?? 0,
    nCompRem: item.nCompRem ?? '',
    cantRem: item.cantRem ?? 0,
    fechaRem: item.fechaRem ?? '',
  };
}

async function fetchConsultaMapped<TApi, TRow>(
  path: string,
  mapper: (item: TApi, index: number) => TRow,
): Promise<ConsultaResult<TRow>> {
  const response = await apiRequest<ConsultaPayload<TApi>>(path);
  const payload = response.resultado;
  const items = payload.items ?? [];

  return {
    items: items.map(mapper),
    meta: extractMeta(payload),
  };
}

export function fetchPedidosIngresados() {
  return fetchConsultaMapped<ApiComprobanteConsultaItem, PedidoConsultaRow>(
    '/consultas/pedidos-ingresados',
    mapComprobanteConsultaItem,
  );
}

export function fetchPedidosPendientes() {
  return fetchConsultaMapped<ApiComprobanteConsultaItem, PedidoConsultaRow>(
    '/consultas/pedidos-pendientes',
    mapComprobanteConsultaItem,
  );
}

export function fetchPresupuestosActivos() {
  return fetchConsultaMapped<ApiComprobanteConsultaItem, PresupuestoConsultaRow>(
    '/consultas/presupuestos?estado=99',
    mapComprobanteConsultaItem,
  );
}

export function fetchPresupuestosCerrados() {
  return fetchConsultaMapped<ApiComprobanteConsultaItem, PresupuestoConsultaRow>(
    '/consultas/presupuestos?estado=98',
    mapComprobanteConsultaItem,
  );
}

export function fetchStock() {
  return fetchConsultaMapped<ApiStockItem, StockConsultaRow>('/consultas/stock', mapStockItem);
}

export type StockPageResult = ConsultaResult<StockConsultaRow> & {
  page: number;
  pageSize: number;
  total: number;
  totalPages: number;
};

export async function fetchStockPage(params: {
  page?: number;
  pageSize?: number;
  q?: string;
} = {}): Promise<StockPageResult> {
  const searchParams = new URLSearchParams();

  if (params.page !== undefined) {
    searchParams.set('page', String(params.page));
  }

  if (params.pageSize !== undefined) {
    searchParams.set('page_size', String(params.pageSize));
  }

  if (params.q) {
    searchParams.set('q', params.q);
  }

  const queryString = searchParams.toString();
  const path = queryString.length > 0 ? `/consultas/stock?${queryString}` : '/consultas/stock';
  const response = await apiRequest<ConsultaPayload<ApiStockItem>>(path);
  const payload = response.resultado;
  const items = payload.items ?? [];

  return {
    items: items.map(mapStockItem),
    meta: extractMeta(payload),
    page: payload.page ?? 1,
    pageSize: payload.page_size ?? items.length,
    total: payload.total ?? items.length,
    totalPages: payload.total_pages ?? 1,
  };
}

export function fetchDeuda() {
  return fetchConsultaMapped<ApiDeudaItem, DeudaConsultaRow>('/consultas/deuda', mapDeudaItem);
}

export function fetchCheques() {
  return fetchConsultaMapped<ApiChequeItem, ChequeConsultaRow>('/consultas/cheques', mapChequeItem);
}

export function fetchHistorialVentas() {
  return fetchConsultaMapped<ApiHistorialItem, HistorialVentasRow>(
    '/consultas/historial-ventas',
    mapHistorialItem,
  );
}

export function fetchDetallePedidos() {
  return fetchConsultaMapped<ApiDetallePedidoItem, DetallePedidoConsultaRow>(
    '/consultas/detalle-pedidos',
    mapDetallePedidoItem,
  );
}

export function toHistorialDetalleRows(row: HistorialVentasRow): HistorialVentasRow[] {
  return [{ ...row, id: `${row.id}-detalle` }];
}
