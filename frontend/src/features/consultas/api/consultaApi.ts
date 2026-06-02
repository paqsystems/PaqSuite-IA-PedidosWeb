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
  estado: number;
  importe: number;
  fecha: string;
  puedeEditar: boolean;
  puedeEliminar: boolean;
  puedeCopiar: boolean;
  puedeConvertir: boolean;
  puedeCerrar: boolean;
  cierre?: PresupuestoCierreInfo;
};

export type PedidoConsultaRow = ComprobanteConsultaRow;

export type PresupuestoConsultaRow = ComprobanteConsultaRow;

export type StockConsultaRow = {
  id: string;
  articulo: string;
  descripcion: string;
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
  codCliente: string;
  articulo: string;
  descripcion: string;
  cantidad: number;
  importe: number;
};

type ApiComprobanteConsultaItem = {
  codPedido?: string;
  codPresupuesto?: string;
  codCliente?: string;
  razonSocial?: string;
  estado?: number;
  fecha?: string;
  numeroVisible?: number;
  total?: number;
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

type ApiStockItem = {
  codArticulo?: string;
  descripcion?: string;
  stock?: number;
  comprometido?: number;
};

type ApiDeudaItem = {
  codCliente?: string;
  fechaVto?: string;
  saldo?: number;
};

type ApiChequeItem = {
  codCliente?: string;
  banco?: string;
  fecha?: string;
  importe?: number;
};

type ApiHistorialItem = {
  codCliente?: string;
  fecha?: string;
  codArticulo?: string;
  descripcion?: string;
  cantidad?: number;
  importe?: number;
};

function extractMeta(payload: ConsultaPayload<unknown>): ConsultaMeta | null {
  return payload.metadata ?? null;
}

function mapComprobanteConsultaItem(item: ApiComprobanteConsultaItem): ComprobanteConsultaRow {
  const codPedido = item.codPedido ?? item.codPresupuesto ?? '';
  const numeroVisible = item.numeroVisible ?? 0;

  return {
    id: codPedido,
    codPedido,
    numero: numeroVisible > 0 ? String(numeroVisible) : codPedido,
    cliente: item.razonSocial ?? item.codCliente ?? '',
    codCliente: item.codCliente ?? '',
    estado: item.estado ?? 0,
    importe: item.total ?? 0,
    fecha: item.fecha ?? '',
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

function mapStockItem(item: ApiStockItem, index: number): StockConsultaRow {
  const codArticulo = item.codArticulo ?? `stock-${index}`;

  return {
    id: codArticulo,
    articulo: codArticulo,
    descripcion: item.descripcion ?? '',
    stockActual: item.stock ?? 0,
    stockComprometido: item.comprometido ?? 0,
  };
}

function mapDeudaItem(item: ApiDeudaItem, index: number): DeudaConsultaRow {
  const codCliente = item.codCliente ?? `deuda-${index}`;

  return {
    id: `${codCliente}-${item.fechaVto ?? index}`,
    cliente: codCliente,
    vencimiento: item.fechaVto ?? '',
    importe: item.saldo ?? 0,
  };
}

function mapChequeItem(item: ApiChequeItem, index: number): ChequeConsultaRow {
  const codCliente = item.codCliente ?? `cheque-${index}`;

  return {
    id: `${codCliente}-${item.fecha ?? index}`,
    cliente: codCliente,
    banco: item.banco ?? '',
    vencimiento: item.fecha ?? '',
    importe: item.importe ?? 0,
  };
}

function mapHistorialItem(item: ApiHistorialItem, index: number): HistorialVentasRow {
  const codCliente = item.codCliente ?? '';
  const codArticulo = item.codArticulo ?? '';

  return {
    id: `${codCliente}-${codArticulo}-${item.fecha ?? index}`,
    fecha: item.fecha ?? '',
    cliente: codCliente,
    codCliente,
    articulo: codArticulo,
    descripcion: item.descripcion ?? '',
    cantidad: item.cantidad ?? 0,
    importe: item.importe ?? 0,
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

export function toHistorialDetalleRows(row: HistorialVentasRow) {
  return [
    {
      id: `${row.id}-detalle`,
      articulo: row.articulo,
      descripcion: row.descripcion,
      cantidad: row.cantidad,
      importe: row.importe,
    },
  ];
}
