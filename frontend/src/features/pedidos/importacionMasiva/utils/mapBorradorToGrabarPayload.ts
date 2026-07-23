import type { GrabarComprobantePayload } from '../../api/comprobanteApi';
import type { BorradorFila } from '../types/importacionMasivaTypes';
import { calcularBonificacionNeta, renglonesValidosParaGrabar } from '../../utils/renglonesCarga';

export function mapBorradorToGrabarPayload(fila: BorradorFila): GrabarComprobantePayload {
  const bonificacionNetaCabecera = calcularBonificacionNeta(
    fila.cabecera.bonif1,
    fila.cabecera.bonif2,
    fila.cabecera.bonif3,
  );

  return {
    accionGrabacion: fila.esPedido ? 'pedido' : 'presupuesto',
    codPedido: null,
    cabecera: {
      ...fila.cabecera,
      descuento: bonificacionNetaCabecera,
    },
    renglones: renglonesValidosParaGrabar(fila.renglones),
  };
}
