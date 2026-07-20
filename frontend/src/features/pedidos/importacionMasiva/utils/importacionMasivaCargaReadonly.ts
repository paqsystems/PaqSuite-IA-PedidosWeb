import type { ComprobanteCabecera } from '../../types/comprobanteCabecera';
import type { ComprobanteRenglon } from '../../api/comprobanteApi';
import { createEmptyRenglon } from '../../utils/renglonesCarga';

export type ImportacionMasivaCargaBorrador = {
  idInterno: string;
  cabecera: ComprobanteCabecera;
  renglones: ComprobanteRenglon[];
  esPedido: boolean;
};

export type ImportacionMasivaCargaNavigationState = {
  mode: 'readonly';
  from: 'importacionMasiva';
  borrador: ImportacionMasivaCargaBorrador;
};

export type ImportacionMasivaCargaHydration = {
  selectedCliente: string;
  cabecera: ComprobanteCabecera;
  renglones: ComprobanteRenglon[];
  estadoActual: number;
};

export function parseImportacionMasivaCargaState(
  state: unknown,
): ImportacionMasivaCargaNavigationState | null {
  if (!state || typeof state !== 'object') {
    return null;
  }

  const candidate = state as Partial<ImportacionMasivaCargaNavigationState>;
  if (candidate.mode !== 'readonly' || candidate.from !== 'importacionMasiva') {
    return null;
  }

  const borrador = candidate.borrador;
  if (!borrador || typeof borrador !== 'object') {
    return null;
  }

  if (typeof borrador.idInterno !== 'string' || typeof borrador.esPedido !== 'boolean') {
    return null;
  }

  if (!borrador.cabecera || typeof borrador.cabecera !== 'object') {
    return null;
  }

  if (!Array.isArray(borrador.renglones)) {
    return null;
  }

  const codCliente = borrador.cabecera.codCliente;
  if (typeof codCliente !== 'string' || codCliente.trim() === '') {
    return null;
  }

  return {
    mode: 'readonly',
    from: 'importacionMasiva',
    borrador: {
      idInterno: borrador.idInterno,
      esPedido: borrador.esPedido,
      cabecera: borrador.cabecera,
      renglones: borrador.renglones,
    },
  };
}

export function buildImportacionMasivaCargaHydration(
  borrador: ImportacionMasivaCargaBorrador,
): ImportacionMasivaCargaHydration {
  return {
    selectedCliente: borrador.cabecera.codCliente,
    cabecera: borrador.cabecera,
    renglones: borrador.renglones.length > 0 ? borrador.renglones : [createEmptyRenglon(1)],
    estadoActual: borrador.esPedido ? 0 : 99,
  };
}
