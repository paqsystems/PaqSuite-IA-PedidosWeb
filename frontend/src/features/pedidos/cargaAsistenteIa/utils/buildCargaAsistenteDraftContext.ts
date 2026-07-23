import type { CargaAsistenteDraftContext } from '../model/cargaAsistenteTypes';

export type BuildCargaAsistenteDraftContextInput = {
  selectedCliente: string | null;
  cabecera: {
    listaPrecios?: number | null;
    observaciones?: string | null;
    nivel?: number | null;
    leyenda1?: string | null;
    leyenda2?: string | null;
    leyenda3?: string | null;
    leyenda4?: string | null;
    leyenda5?: string | null;
    [key: string]: unknown;
  } | null;
  renglones: Array<{
    renglon?: number;
    codArticulo: string;
    cantidad: number;
    precio?: number;
    porcBonif?: number;
    descripcionArticulo?: string;
    descripcion?: string;
  }>;
  readOnly: boolean;
  modo: string;
  perfilUsuario: 'V' | 'S' | 'C';
};

export function buildCargaAsistenteDraftContext(
  input: BuildCargaAsistenteDraftContextInput,
): CargaAsistenteDraftContext {
  const listaPrecios = input.cabecera?.listaPrecios ?? null;
  const codLista =
    listaPrecios !== null && listaPrecios !== undefined && !Number.isNaN(Number(listaPrecios))
      ? Number(listaPrecios)
      : 0;

  return {
    modo: input.readOnly ? 'soloLectura' : input.modo === 'editar' ? 'edicion' : input.modo,
    perfilUsuario: input.perfilUsuario,
    codCliente: input.selectedCliente,
    cabecera: input.cabecera
      ? {
          listaPrecios: input.cabecera.listaPrecios ?? null,
          observaciones: input.cabecera.observaciones ?? null,
          nivel: input.cabecera.nivel ?? null,
          leyenda1: input.cabecera.leyenda1 ?? null,
          leyenda2: input.cabecera.leyenda2 ?? null,
          leyenda3: input.cabecera.leyenda3 ?? null,
          leyenda4: input.cabecera.leyenda4 ?? null,
          leyenda5: input.cabecera.leyenda5 ?? null,
        }
      : {},
    renglones: input.renglones
      .filter((renglon) => String(renglon.codArticulo ?? '').trim() !== '')
      .map((renglon, index) => ({
        renglon:
          renglon.renglon !== undefined && Number.isFinite(Number(renglon.renglon))
            ? Number(renglon.renglon)
            : index + 1,
        codArticulo: String(renglon.codArticulo),
        cantidad: Number(renglon.cantidad) || 0,
        precio: renglon.precio,
        porcBonif: renglon.porcBonif,
        descripcion: renglon.descripcionArticulo ?? renglon.descripcion,
      })),
    readOnly: input.readOnly,
    codLista,
  };
}

export function mapFunctionalProfileToPerfilUsuario(
  functionalProfile: string,
): 'V' | 'S' | 'C' {
  if (functionalProfile === 'cliente') {
    return 'C';
  }

  if (functionalProfile === 'supervisor') {
    return 'S';
  }

  return 'V';
}
