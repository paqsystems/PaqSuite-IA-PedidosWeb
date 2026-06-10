/** Valores de `pq_pedidosweb_listaprecios.moneda` / cabecera.moneda */
export const monedaCabeceraOptions = [
  { codigo: 0, labelKey: 'pedidos.carga.moneda.extranjera' },
  { codigo: 1, labelKey: 'pedidos.carga.moneda.corriente' },
] as const;

export const bonificacionCabeceraFormat = '#,##0.00';

export const bonificacionCabecera3Min = -99.99;
export const bonificacionCabecera3Max = 99.99;
