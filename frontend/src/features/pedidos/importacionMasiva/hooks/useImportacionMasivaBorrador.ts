import { useCallback, useEffect, useMemo, useState } from 'react';
import type { BorradorFila } from '../types/importacionMasivaTypes';
import {
  calcularBonificacionNeta,
  calcularTotalesComprobante,
} from '../../utils/renglonesCarga';
import {
  clearImportacionMasivaBorradorStorage,
  persistImportacionMasivaBorrador,
  readImportacionMasivaBorradorSnapshot,
} from '../utils/importacionMasivaBorradorStorage';

function recalcularFila(fila: BorradorFila): BorradorFila {
  const descuento = calcularBonificacionNeta(fila.cabecera.bonif1, fila.cabecera.bonif2, fila.cabecera.bonif3);
  const cabecera = { ...fila.cabecera, descuento };
  const totales = calcularTotalesComprobante(fila.renglones, descuento);

  return {
    ...fila,
    cabecera,
    cantidadRenglones: fila.renglones.length,
    totalImporte: totales.total,
  };
}

export function useImportacionMasivaBorrador() {
  const [filas, setFilas] = useState<BorradorFila[]>(
    () => readImportacionMasivaBorradorSnapshot()?.filas ?? [],
  );

  useEffect(() => {
    persistImportacionMasivaBorrador(filas);
  }, [filas]);

  const tieneFilasPendientes = filas.length > 0;

  const replaceFilas = useCallback((nextFilas: BorradorFila[]) => {
    setFilas(nextFilas.map(recalcularFila));
  }, []);

  const appendFilas = useCallback((nextFilas: BorradorFila[]) => {
    setFilas((current) => [...current, ...nextFilas.map(recalcularFila)]);
  }, []);

  const removeFila = useCallback((idInterno: string) => {
    setFilas((current) => current.filter((fila) => fila.idInterno !== idInterno));
  }, []);

  const clearFilas = useCallback(() => {
    setFilas([]);
    clearImportacionMasivaBorradorStorage();
  }, []);

  const setEsPedido = useCallback((idInterno: string, esPedido: boolean) => {
    setFilas((current) =>
      current.map((fila) => (fila.idInterno === idInterno ? { ...fila, esPedido } : fila)),
    );
  }, []);

  const setTodasEsPedido = useCallback((esPedido: boolean) => {
    setFilas((current) => current.map((fila) => ({ ...fila, esPedido })));
  }, []);

  const setErrorFila = useCallback((idInterno: string, errorGrabacion: string) => {
    setFilas((current) =>
      current.map((fila) => (fila.idInterno === idInterno ? { ...fila, errorGrabacion } : fila)),
    );
  }, []);

  const filasGrid = useMemo(
    () =>
      filas.map((fila) => ({
        ...fila,
        id: fila.idInterno,
      })),
    [filas],
  );

  return {
    filas,
    filasGrid,
    tieneFilasPendientes,
    replaceFilas,
    appendFilas,
    removeFila,
    clearFilas,
    setEsPedido,
    setTodasEsPedido,
    setErrorFila,
  };
}
