import type { ComprobanteRenglon } from '../api/comprobanteApi';

export function createEmptyRenglon(renglon: number): ComprobanteRenglon {
  return {
    renglon,
    codArticulo: '',
    descripcionArticulo: '',
    cantidad: 1,
    precio: 0,
    porcBonif: 0,
    porcIva: 21,
  };
}

export function nextRenglonNumber(renglones: ComprobanteRenglon[]): number {
  return renglones.reduce((max, renglon) => Math.max(max, renglon.renglon), 0) + 1;
}

export function calcularBonificacionNeta(bonif1: number, bonif2: number, bonif3: number): number {
  const factor = (1 - bonif1 / 100) * (1 - bonif2 / 100) * (1 - bonif3 / 100);

  return Math.round((1 - factor) * 10000) / 100;
}

export function redondearImporte(valor: number): number {
  return Math.round(valor * 100) / 100;
}

export function redondearPrecioUnitario(valor: number): number {
  return Math.round(valor * 10000) / 10000;
}

/** Precio lista × (1 − bonif. renglón / 100) × (1 − bonif. neta cabecera / 100), 4 decimales. */
export function calcularPrecioNetoUnitario(
  precioLista: number,
  porcBonifRenglon: number,
  bonificacionNetaCabecera: number,
): number {
  return redondearPrecioUnitario(
    precioLista * (1 - porcBonifRenglon / 100) * (1 - bonificacionNetaCabecera / 100),
  );
}

/** Normaliza `porc_iva` del ERP a escala 0–100 (ej. 0.21 → 21). */
export function normalizarPorcIvaAlmacenado(porcIva: number): number {
  const valor = Math.abs(porcIva);
  if (valor > 0 && valor < 1) {
    return redondearImporte(valor * 100);
  }

  return porcIva;
}

/** Factor multiplicador de IVA (21 → 0.21). Siempre divide por 100 cuando el valor es porcentaje. */
export function factorPorcIva(porcIva: number): number {
  const porcentaje = Math.abs(normalizarPorcIvaAlmacenado(porcIva));
  if (porcentaje === 0) {
    return 0;
  }

  return porcentaje / 100;
}

/** Precio × cantidad × (1 − bonif. renglón / 100), 2 decimales. */
export function calcularImporteBrutoRenglon(renglon: ComprobanteRenglon): number {
  return redondearImporte(
    renglon.cantidad * renglon.precio * (1 - renglon.porcBonif / 100),
  );
}

/** Precio × cantidad × (1 − bonif. renglón / 100) × (1 − bonif. neta cabecera / 100), 2 decimales. */
export function calcularImporteNetoRenglon(
  renglon: ComprobanteRenglon,
  bonificacionNetaCabecera: number,
): number {
  return redondearImporte(
    renglon.cantidad *
      renglon.precio *
      (1 - renglon.porcBonif / 100) *
      (1 - bonificacionNetaCabecera / 100),
  );
}

export function calcularImporteIvaRenglon(
  renglon: ComprobanteRenglon,
  bonificacionNetaCabecera: number,
): number {
  const neto = calcularImporteNetoRenglon(renglon, bonificacionNetaCabecera);

  return redondearImporte(neto * factorPorcIva(renglon.porcIva));
}

export function calcularImporteNetoConIvaRenglon(
  renglon: ComprobanteRenglon,
  bonificacionNetaCabecera: number,
): number {
  const neto = calcularImporteNetoRenglon(renglon, bonificacionNetaCabecera);
  const iva = calcularImporteIvaRenglon(renglon, bonificacionNetaCabecera);

  return redondearImporte(neto + iva);
}

export type TotalesComprobanteCarga = {
  subtotal: number;
  iva: number;
  total: number;
};

/** Subtotal = Σ importe neto; IVA = Σ importe IVA; total = Σ importe neto c/IVA. */
export function calcularTotalesComprobante(
  renglones: ComprobanteRenglon[],
  bonificacionNetaCabecera: number,
): TotalesComprobanteCarga {
  return renglonesValidosParaGrabar(renglones).reduce<TotalesComprobanteCarga>(
    (acc, renglon) => {
      const neto = calcularImporteNetoRenglon(renglon, bonificacionNetaCabecera);
      const ivaRenglon = calcularImporteIvaRenglon(renglon, bonificacionNetaCabecera);
      const totalRenglon = calcularImporteNetoConIvaRenglon(renglon, bonificacionNetaCabecera);

      return {
        subtotal: redondearImporte(acc.subtotal + neto),
        iva: redondearImporte(acc.iva + ivaRenglon),
        total: redondearImporte(acc.total + totalRenglon),
      };
    },
    { subtotal: 0, iva: 0, total: 0 },
  );
}

/** @deprecated Usar calcularImporteBrutoRenglon */
export function calcularSubtotalRenglon(renglon: ComprobanteRenglon): number {
  return calcularImporteBrutoRenglon(renglon);
}

export function formatImporteMoneda(simbolo: string, valor: number): string {
  const formatted = valor.toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  return `${simbolo} ${formatted}`;
}

export function renglonesValidosParaGrabar(renglones: ComprobanteRenglon[]): ComprobanteRenglon[] {
  return renglones.filter((renglon) => renglon.codArticulo.trim() !== '');
}

export function tieneRenglonesCargados(renglones: ComprobanteRenglon[]): boolean {
  return renglonesValidosParaGrabar(renglones).length > 0;
}
