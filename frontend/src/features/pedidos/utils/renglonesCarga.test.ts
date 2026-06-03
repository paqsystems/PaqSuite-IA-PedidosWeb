import { describe, expect, it } from 'vitest';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import {
  calcularImporteBrutoRenglon,
  calcularImporteIvaRenglon,
  calcularImporteNetoConIvaRenglon,
  calcularImporteNetoRenglon,
  calcularTotalesComprobante,
  factorPorcIva,
  normalizarPorcIvaAlmacenado,
} from './renglonesCarga';

const renglonBase: ComprobanteRenglon = {
  renglon: 1,
  codArticulo: 'AC01',
  descripcionArticulo: 'Test',
  cantidad: 10,
  precio: 100,
  porcBonif: 10,
  porcIva: 21,
};

describe('renglonesCarga importes', () => {
  it('calcula importe bruto con bonificación de renglón', () => {
    expect(calcularImporteBrutoRenglon(renglonBase)).toBe(900);
  });

  it('calcula importe neto con bonificación neta de cabecera', () => {
    expect(calcularImporteNetoRenglon(renglonBase, 5)).toBe(855);
  });

  it('calcula IVA y neto con IVA sobre importe neto', () => {
    expect(calcularImporteIvaRenglon(renglonBase, 0)).toBe(189);
    expect(calcularImporteNetoConIvaRenglon(renglonBase, 0)).toBe(1089);
  });

  it('normaliza porc IVA fracción ERP y aplica factor /100', () => {
    expect(normalizarPorcIvaAlmacenado(0.21)).toBe(21);
    expect(factorPorcIva(0.21)).toBeCloseTo(0.21);
    expect(factorPorcIva(21)).toBeCloseTo(0.21);
  });

  it('suma totales por renglón redondeado', () => {
    const totales = calcularTotalesComprobante([renglonBase, { ...renglonBase, renglon: 2 }], 0);

    expect(totales.subtotal).toBe(1800);
    expect(totales.iva).toBe(378);
    expect(totales.total).toBe(2178);
  });
});
