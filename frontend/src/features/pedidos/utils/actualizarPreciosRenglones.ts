import { fetchPreciosArticulosPorLista } from '../api/comprobanteApi';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import { renglonesValidosParaGrabar } from './renglonesCarga';

export async function actualizarPreciosRenglonesPorLista(
  renglones: ComprobanteRenglon[],
  codLista: number,
): Promise<ComprobanteRenglon[]> {
  const renglonesActivos = renglonesValidosParaGrabar(renglones);
  if (renglonesActivos.length === 0) {
    return renglones;
  }

  const precios = await fetchPreciosArticulosPorLista(
    renglonesActivos.map((renglon) => renglon.codArticulo),
    codLista,
  );
  const precioPorCodigo = new Map(precios.map((item) => [item.codArticulo, item.precio]));

  return renglones.map((renglon) => {
    if (renglon.codArticulo.trim() === '') {
      return renglon;
    }

    const precio = precioPorCodigo.get(renglon.codArticulo);
    if (precio === undefined) {
      return renglon;
    }

    return { ...renglon, precio };
  });
}
