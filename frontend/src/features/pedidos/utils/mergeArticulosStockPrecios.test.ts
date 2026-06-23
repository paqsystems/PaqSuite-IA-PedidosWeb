import { describe, expect, it } from 'vitest';
import type { ArticuloOption } from '../api/comprobanteApi';
import { mergeArticulosStockPrecios } from './mergeArticulosStockPrecios';

const stockBase: ArticuloOption[] = [
  {
    codArticulo: 'A1',
    descripcion: 'Artículo uno',
    precio: 0,
    bonificacion: 5,
    porcIva: 21,
    disponibleNeto: 10,
    disponibleNetoBase: null,
  },
  {
    codArticulo: 'A2',
    descripcion: 'Artículo dos',
    precio: 0,
    bonificacion: 0,
    porcIva: 10.5,
    disponibleNeto: 3,
    disponibleNetoBase: 1,
  },
];

describe('mergeArticulosStockPrecios', () => {
  it('devuelve stock sin cambios si no hay precios', () => {
    expect(mergeArticulosStockPrecios(stockBase, [])).toEqual(stockBase);
  });

  it('aplica precio y mantiene disponible del stock', () => {
    const precios: ArticuloOption[] = [
      {
        codArticulo: 'A1',
        descripcion: 'Artículo uno',
        precio: 99.5,
        bonificacion: 7,
        porcIva: 21,
      },
    ];

    const merged = mergeArticulosStockPrecios(stockBase, precios);

    expect(merged[0]).toMatchObject({
      codArticulo: 'A1',
      precio: 99.5,
      bonificacion: 7,
      disponibleNeto: 10,
    });
    expect(merged[1]).toMatchObject({
      codArticulo: 'A2',
      precio: 0,
      disponibleNeto: 3,
      disponibleNetoBase: 1,
    });
  });
});
