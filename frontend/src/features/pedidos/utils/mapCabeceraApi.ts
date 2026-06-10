import type { CabeceraCatalogos, ComprobanteCabecera } from '../types/comprobanteCabecera';
import { emptyComprobanteCabecera } from '../types/comprobanteCabecera';

type ApiCabeceraRow = {
  cod_cliente?: string;
  cod_vended?: string | null;
  vendedor_nombre?: string;
  cod_condvta?: number | null;
  cod_transpor?: string | null;
  id_de?: number | null;
  direccion_entrega?: string;
  expreso?: string | null;
  expreso_dire?: string | null;
  nivel?: number;
  lista_precios?: number | null;
  lista_precios_descripcion?: string;
  moneda?: number;
  incluye_iva?: boolean;
  bonif_1?: number;
  bonif_2?: number;
  bonif_3?: number;
  descuento?: number;
  observaciones?: string | null;
  cod_perfil?: string | null;
  leyenda_1?: string | null;
  leyenda_2?: string | null;
  leyenda_3?: string | null;
  leyenda_4?: string | null;
  leyenda_5?: string | null;
  fecha_entrega?: string | null;
};

export function mapCabeceraFromApi(row: ApiCabeceraRow, codClienteFallback: string): ComprobanteCabecera {
  const base = emptyComprobanteCabecera(row.cod_cliente ?? codClienteFallback);

  return {
    ...base,
    codCliente: row.cod_cliente ?? base.codCliente,
    codVended: row.cod_vended ?? null,
    vendedorNombre: row.vendedor_nombre ?? '',
    codCondvta: row.cod_condvta ?? null,
    codTranspor: row.cod_transpor ?? null,
    idDe: row.id_de ?? null,
    direccionEntrega: row.direccion_entrega ?? '',
    expreso: row.expreso ?? null,
    expresoDire: row.expreso_dire ?? null,
    nivel: row.nivel ?? 0,
    listaPrecios: row.lista_precios ?? null,
    listaPreciosDescripcion: row.lista_precios_descripcion ?? '',
    moneda: row.moneda ?? 1,
    incluyeIva: row.incluye_iva ?? false,
    bonif1: row.bonif_1 ?? 0,
    bonif2: row.bonif_2 ?? 0,
    bonif3: row.bonif_3 ?? 0,
    descuento: row.descuento ?? 0,
    observaciones: row.observaciones ?? '',
    codPerfil: row.cod_perfil ?? null,
    leyenda1: row.leyenda_1 ?? null,
    leyenda2: row.leyenda_2 ?? null,
    leyenda3: row.leyenda_3 ?? null,
    leyenda4: row.leyenda_4 ?? null,
    leyenda5: row.leyenda_5 ?? null,
    fechaEntrega: row.fecha_entrega ?? null,
  };
}

export function mapCabeceraToApi(cabecera: ComprobanteCabecera) {
  return {
    cod_cliente: cabecera.codCliente,
    cod_vended: cabecera.codVended,
    cod_condvta: cabecera.codCondvta,
    cod_transpor: cabecera.codTranspor,
    id_de: cabecera.idDe,
    nivel: cabecera.nivel,
    lista_precios: cabecera.listaPrecios,
    moneda: cabecera.moneda,
    incluye_iva: cabecera.incluyeIva,
    bonif_1: cabecera.bonif1,
    bonif_2: cabecera.bonif2,
    bonif_3: cabecera.bonif3,
    descuento: cabecera.descuento,
    observaciones: cabecera.observaciones,
    cod_perfil: cabecera.codPerfil,
    expreso: cabecera.expreso,
    expreso_dire: cabecera.expresoDire,
    fecha_entrega: cabecera.fechaEntrega,
    leyenda_1: cabecera.leyenda1,
    leyenda_2: cabecera.leyenda2,
    leyenda_3: cabecera.leyenda3,
    leyenda_4: cabecera.leyenda4,
    leyenda_5: cabecera.leyenda5,
  };
}

export function mapCatalogosFromApi(raw: Partial<CabeceraCatalogos> | undefined): CabeceraCatalogos {
  return {
    condicionesVenta: raw?.condicionesVenta ?? [],
    transportes: raw?.transportes ?? [],
    listasPrecios: raw?.listasPrecios ?? [],
    direccionesEntrega: raw?.direccionesEntrega ?? [],
    perfiles: raw?.perfiles ?? [],
  };
}
