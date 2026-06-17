import type { ComprobanteRenglon } from '../api/comprobanteApi';
import { mapCabeceraFromApi } from './mapCabeceraApi';
import type { ComprobanteCabecera } from '../types/comprobanteCabecera';
import { normalizarPorcIvaAlmacenado, renglonesValidosParaGrabar } from './renglonesCarga';

export type ExcelImportRowPayload = Record<string, unknown>;

export function mapExcelRowToCabecera(
  row: ExcelImportRowPayload,
  base: ComprobanteCabecera,
): ComprobanteCabecera {
  return mapCabeceraFromApi(
    {
      cod_cliente: row.cod_cliente as string | undefined,
      cod_condvta: row.cod_condvta as number | null | undefined,
      cod_transpor: row.cod_transpor as string | null | undefined,
      id_de: row.id_de as number | null | undefined,
      nivel: row.nivel as number | undefined,
      lista_precios: row.cod_lista as number | null | undefined,
      bonif_1: row.bonif1 as number | undefined,
      bonif_2: row.bonif2 as number | undefined,
      bonif_3: row.bonif3 as number | undefined,
      expreso: row.expreso as string | null | undefined,
      expreso_dire: row.expreso_dire as string | null | undefined,
      fecha_entrega: row.fecha_entrega as string | null | undefined,
      observaciones: row.observaciones as string | null | undefined,
      cod_perfil: row.cod_perfil as string | null | undefined,
      leyenda_1: row.leyenda1 as string | null | undefined,
      leyenda_2: row.leyenda2 as string | null | undefined,
      leyenda_3: row.leyenda3 as string | null | undefined,
      leyenda_4: row.leyenda4 as string | null | undefined,
      leyenda_5: row.leyenda5 as string | null | undefined,
    },
    base.codCliente,
  );
}

export function mapExcelRowsToRenglones(rows: ExcelImportRowPayload[]): ComprobanteRenglon[] {
  return rows.map((row, index) => ({
    renglon: index + 1,
    codArticulo: String(row.cod_articulo ?? ''),
    descripcionArticulo: String(row.descripcion_articulo ?? ''),
    cantidad: Number(row.cantidad ?? 0),
    precio: Number(row.precio ?? 0),
    porcBonif: Number(row.porc_bonif ?? 0),
    porcIva: normalizarPorcIvaAlmacenado(Number(row.porc_iva ?? 0)),
  }));
}

export type PedidosCargaExcelImportDisabledInput = {
  excelImportEnabled: boolean;
  readOnly: boolean;
  modo: string;
  comprobanteId: string | null;
  renglones: ComprobanteRenglon[];
  isClienteProfile: boolean;
  selectedCliente: string | null;
};

export function isPedidosCargaExcelImportDisabled(input: PedidosCargaExcelImportDisabledInput): boolean {
  const tieneRenglonesCargados = renglonesValidosParaGrabar(input.renglones).length > 0;

  return (
    !input.excelImportEnabled ||
    input.readOnly ||
    input.modo !== 'nuevo' ||
    Boolean(input.comprobanteId) ||
    tieneRenglonesCargados ||
    (!input.isClienteProfile && input.selectedCliente !== null)
  );
}
