export function resolveEquivalenciaVentas(equivalenciaVentas?: number | null): number {
  const value = Number(equivalenciaVentas ?? 0);
  return value > 0 ? value : 1;
}

export function fromCantidadUsuario(
  cantidadUsuario: number,
  equivalenciaVentas: number | null | undefined,
  cargaUnidadesVenta: boolean,
): { cantidad: number; cantidadVenta: number } {
  const equiv = resolveEquivalenciaVentas(equivalenciaVentas);
  const usuario = Number(cantidadUsuario) || 0;

  if (cargaUnidadesVenta) {
    const cantidadVenta = usuario;
    return {
      cantidad: Number((cantidadVenta * equiv).toFixed(4)),
      cantidadVenta: Number(cantidadVenta.toFixed(4)),
    };
  }

  const cantidad = usuario;
  return {
    cantidad: Number(cantidad.toFixed(4)),
    cantidadVenta: Number((cantidad / equiv).toFixed(4)),
  };
}

export function cantidadVisibleParaUsuario(
  cantidad: number,
  cantidadVenta: number | null | undefined,
  cargaUnidadesVenta: boolean,
): number {
  if (cargaUnidadesVenta) {
    return Number(cantidadVenta ?? cantidad) || 0;
  }

  return Number(cantidad) || 0;
}

export function applyCantidadUsuarioToRenglon<
  T extends {
    cantidad: number;
    cantidadVenta?: number;
    equivalenciaVentas?: number;
  },
>(
  renglon: T,
  cantidadUsuario: number,
  cargaUnidadesVenta: boolean,
): T {
  const pair = fromCantidadUsuario(
    cantidadUsuario,
    renglon.equivalenciaVentas,
    cargaUnidadesVenta,
  );

  return {
    ...renglon,
    cantidad: pair.cantidad,
    cantidadVenta: pair.cantidadVenta,
  };
}

export function enrichRenglonesEquivalenciaVentas<
  T extends { codArticulo: string; equivalenciaVentas?: number },
>(
  renglones: T[],
  articulos: Array<{ codArticulo: string; equivalenciaVentas?: number }>,
): T[] {
  const equivByCodigo = new Map(
    articulos.map((articulo) => [
      articulo.codArticulo,
      resolveEquivalenciaVentas(articulo.equivalenciaVentas),
    ]),
  );

  let changed = false;
  const next = renglones.map((renglon) => {
    if (renglon.equivalenciaVentas != null && Number(renglon.equivalenciaVentas) > 0) {
      return renglon;
    }

    const equivalenciaVentas = equivByCodigo.get(renglon.codArticulo);
    if (equivalenciaVentas === undefined) {
      return renglon;
    }

    changed = true;
    return { ...renglon, equivalenciaVentas };
  });

  return changed ? next : renglones;
}
