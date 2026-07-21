import type { ComprobanteCabecera } from '../../types/comprobanteCabecera';

/**
 * Completa atributos vacíos del borrador masivo con defaults de cabecera inicial del cliente,
 * sin pisar valores ya traídos por el Excel/borrador.
 */
export function mergeImportacionMasivaCabeceraWithInicial(
  borradorCabecera: ComprobanteCabecera,
  inicialCabecera: ComprobanteCabecera,
): ComprobanteCabecera {
  return {
    ...inicialCabecera,
    ...borradorCabecera,
    codCliente: borradorCabecera.codCliente || inicialCabecera.codCliente,
    razonSocial: borradorCabecera.razonSocial || inicialCabecera.razonSocial,
    codVended: borradorCabecera.codVended ?? inicialCabecera.codVended,
    vendedorNombre: borradorCabecera.vendedorNombre || inicialCabecera.vendedorNombre,
    codCondvta: isPositiveNumber(borradorCabecera.codCondvta)
      ? borradorCabecera.codCondvta
      : inicialCabecera.codCondvta,
    codTranspor: borradorCabecera.codTranspor || inicialCabecera.codTranspor,
    idDe: isPositiveNumber(borradorCabecera.idDe) ? borradorCabecera.idDe : inicialCabecera.idDe,
    direccionEntrega: borradorCabecera.direccionEntrega || inicialCabecera.direccionEntrega,
    listaPrecios: isPositiveNumber(borradorCabecera.listaPrecios)
      ? borradorCabecera.listaPrecios
      : inicialCabecera.listaPrecios,
    listaPreciosDescripcion:
      borradorCabecera.listaPreciosDescripcion || inicialCabecera.listaPreciosDescripcion,
    moneda: borradorCabecera.listaPrecios
      ? borradorCabecera.moneda
      : inicialCabecera.moneda,
    incluyeIva: borradorCabecera.listaPrecios
      ? borradorCabecera.incluyeIva
      : inicialCabecera.incluyeIva,
    codPerfil: borradorCabecera.codPerfil || inicialCabecera.codPerfil,
    expreso: borradorCabecera.expreso ?? inicialCabecera.expreso,
    expresoDire: borradorCabecera.expresoDire ?? inicialCabecera.expresoDire,
    fechaEntrega: borradorCabecera.fechaEntrega ?? inicialCabecera.fechaEntrega,
    observaciones: borradorCabecera.observaciones || inicialCabecera.observaciones,
    leyenda1: borradorCabecera.leyenda1 ?? inicialCabecera.leyenda1,
    leyenda2: borradorCabecera.leyenda2 ?? inicialCabecera.leyenda2,
    leyenda3: borradorCabecera.leyenda3 ?? inicialCabecera.leyenda3,
    leyenda4: borradorCabecera.leyenda4 ?? inicialCabecera.leyenda4,
    leyenda5: borradorCabecera.leyenda5 ?? inicialCabecera.leyenda5,
  };
}

function isPositiveNumber(value: number | null | undefined): value is number {
  return value !== null && value !== undefined && !Number.isNaN(Number(value)) && Number(value) > 0;
}
