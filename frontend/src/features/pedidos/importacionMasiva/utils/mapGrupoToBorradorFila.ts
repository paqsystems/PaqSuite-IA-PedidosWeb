import type { ComprobanteRenglon } from '../../api/comprobanteApi';
import { mapCabeceraFromApi } from '../../utils/mapCabeceraApi';
import {
  calcularBonificacionNeta,
  calcularTotalesComprobante,
  normalizarPorcIvaAlmacenado,
} from '../../utils/renglonesCarga';
import type { BorradorFila, ImportacionMasivaGrupoApi } from '../types/importacionMasivaTypes';

function mapRenglonFromGrupo(row: Record<string, unknown>, index: number): ComprobanteRenglon {
  return {
    renglon: Number(row.renglon ?? index + 1),
    codArticulo: String(row.cod_articulo ?? ''),
    descripcionArticulo: String(row.descripcion_articulo ?? ''),
    cantidad: Number(row.cantidad ?? 0),
    precio: Number(row.precio ?? 0),
    porcBonif: Number(row.porc_bonif ?? 0),
    porcIva: normalizarPorcIvaAlmacenado(Number(row.porc_iva ?? 0)),
  };
}

export function mapGrupoToBorradorFila(grupo: ImportacionMasivaGrupoApi, idInterno: string): BorradorFila {
  const codCliente = String(grupo.clave.codCliente ?? grupo.cabecera.cod_cliente ?? '');
  const cabecera = mapCabeceraFromApi(grupo.cabecera, codCliente);
  cabecera.codVended = grupo.vendedor.codVended || cabecera.codVended;
  cabecera.vendedorNombre = grupo.vendedor.nombre || cabecera.vendedorNombre;
  cabecera.nivel = grupo.clave.nivel ?? cabecera.nivel;
  cabecera.descuento = calcularBonificacionNeta(cabecera.bonif1, cabecera.bonif2, cabecera.bonif3);

  const renglones = (grupo.renglones ?? []).map(mapRenglonFromGrupo);
  const totales = calcularTotalesComprobante(renglones, cabecera.descuento);

  return {
    idInterno,
    esPedido: true,
    cabecera,
    renglones,
    errorGrabacion: null,
    cantidadRenglones: renglones.length,
    totalImporte: totales.total,
  };
}

export function mapGruposToBorradorFilas(
  grupos: ImportacionMasivaGrupoApi[],
  createIdInterno: () => string = crypto.randomUUID.bind(crypto),
): BorradorFila[] {
  return grupos.map((grupo) => mapGrupoToBorradorFila(grupo, createIdInterno()));
}
