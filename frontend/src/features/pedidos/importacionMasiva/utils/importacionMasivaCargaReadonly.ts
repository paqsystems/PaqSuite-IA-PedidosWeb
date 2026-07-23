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

export const IMPORTACION_MASIVA_CONSULT_QUERY = 'imConsult';

const CONSULT_STORAGE_PREFIX = 'pw.importacionMasiva.consultar.';

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

  const codClienteRaw = borrador.cabecera.codCliente;
  const codCliente =
    typeof codClienteRaw === 'string' || typeof codClienteRaw === 'number'
      ? String(codClienteRaw).trim()
      : '';
  if (codCliente === '') {
    return null;
  }

  return {
    mode: 'readonly',
    from: 'importacionMasiva',
    borrador: {
      idInterno: borrador.idInterno,
      esPedido: borrador.esPedido,
      cabecera: {
        ...borrador.cabecera,
        codCliente,
      },
      renglones: borrador.renglones,
    },
  };
}

/** Usa localStorage (compartido entre solapas); sessionStorage no se hereda en window.open. */
const consultPayloadCache = new Map<string, ImportacionMasivaCargaNavigationState>();

export function storeImportacionMasivaConsultPayload(
  state: ImportacionMasivaCargaNavigationState,
): string {
  const key = `${CONSULT_STORAGE_PREFIX}${crypto.randomUUID()}`;
  localStorage.setItem(key, JSON.stringify(state));
  consultPayloadCache.set(key, state);
  return key;
}

export function readImportacionMasivaConsultPayload(
  storageKey: string,
): ImportacionMasivaCargaNavigationState | null {
  if (!storageKey.startsWith(CONSULT_STORAGE_PREFIX)) {
    return null;
  }

  const cached = consultPayloadCache.get(storageKey);
  if (cached) {
    return cached;
  }

  try {
    const raw = localStorage.getItem(storageKey);
    if (!raw) {
      return null;
    }

    const parsed = parseImportacionMasivaCargaState(JSON.parse(raw) as unknown);
    if (parsed) {
      consultPayloadCache.set(storageKey, parsed);
    }
    return parsed;
  } catch {
    return null;
  }
}

export function clearImportacionMasivaConsultPayload(storageKey: string): void {
  if (!storageKey.startsWith(CONSULT_STORAGE_PREFIX)) {
    return;
  }

  consultPayloadCache.delete(storageKey);
  localStorage.removeItem(storageKey);
}

export function resolveImportacionMasivaCargaContext(input: {
  locationState: unknown;
  consultStorageKey: string | null;
}): ImportacionMasivaCargaNavigationState | null {
  const fromState = parseImportacionMasivaCargaState(input.locationState);
  if (fromState) {
    return fromState;
  }

  if (!input.consultStorageKey) {
    return null;
  }

  return readImportacionMasivaConsultPayload(input.consultStorageKey);
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
