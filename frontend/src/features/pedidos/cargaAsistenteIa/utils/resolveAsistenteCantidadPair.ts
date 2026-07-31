import { fromCantidadUsuario, resolveEquivalenciaVentas } from '../../utils/cargaUnidadesVenta';

export type AsistenteCantidadPayload = {
  cantidad: number;
  cantidadVenta?: number;
  equivalenciaVentas?: number;
};

/**
 * Normaliza el par cantidad/cantidadVenta recibido del asistente.
 * Si el backend ya envió ambos, se confía en ese par; si falta cantidadVenta
 * y el modo es unidades de venta, se deriva con la equivalencia.
 */
export function resolveAsistenteCantidadPair(
  payload: AsistenteCantidadPayload,
  cargaUnidadesVenta: boolean,
): { cantidad: number; cantidadVenta: number; equivalenciaVentas: number } {
  const equivalenciaVentas = resolveEquivalenciaVentas(payload.equivalenciaVentas);
  const cantidad = Number(payload.cantidad) > 0 ? Number(payload.cantidad) : 1;

  if (payload.cantidadVenta !== undefined && Number.isFinite(Number(payload.cantidadVenta))) {
    return {
      cantidad,
      cantidadVenta: Number(payload.cantidadVenta),
      equivalenciaVentas,
    };
  }

  const pair = fromCantidadUsuario(cantidad, equivalenciaVentas, cargaUnidadesVenta);

  return {
    cantidad: pair.cantidad,
    cantidadVenta: pair.cantidadVenta,
    equivalenciaVentas,
  };
}
