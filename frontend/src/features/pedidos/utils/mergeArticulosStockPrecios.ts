import type { ArticuloOption } from '../api/comprobanteApi';

/** Combina catálogo con stock (carga inicial) y precios por lista (tras elegir cliente). */
export function mergeArticulosStockPrecios(
  stockItems: ArticuloOption[],
  precioItems: ArticuloOption[],
): ArticuloOption[] {
  if (precioItems.length === 0) {
    return stockItems;
  }

  const precioPorCodigo = new Map(precioItems.map((item) => [item.codArticulo, item]));

  return stockItems.map((stock) => {
    const precios = precioPorCodigo.get(stock.codArticulo);
    if (!precios) {
      return stock;
    }

    return {
      ...stock,
      precio: precios.precio ?? stock.precio,
      bonificacion: precios.bonificacion ?? stock.bonificacion,
      porcIva: precios.porcIva ?? stock.porcIva,
    };
  });
}
