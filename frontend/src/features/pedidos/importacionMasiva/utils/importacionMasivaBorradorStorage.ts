import { IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY } from '../constants';
import type { BorradorFila, ImportacionMasivaBorradorSnapshot } from '../types/importacionMasivaTypes';

export function readImportacionMasivaBorradorSnapshot(): ImportacionMasivaBorradorSnapshot | null {
  try {
    const raw = sessionStorage.getItem(IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY);
    if (!raw) {
      return null;
    }

    const parsed = JSON.parse(raw) as ImportacionMasivaBorradorSnapshot;
    if (!Array.isArray(parsed.filas)) {
      return null;
    }

    return parsed;
  } catch {
    return null;
  }
}

export function persistImportacionMasivaBorrador(filas: BorradorFila[]): void {
  if (filas.length === 0) {
    sessionStorage.removeItem(IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY);
    return;
  }

  sessionStorage.setItem(
    IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY,
    JSON.stringify({ filas } satisfies ImportacionMasivaBorradorSnapshot),
  );
}

export function clearImportacionMasivaBorradorStorage(): void {
  sessionStorage.removeItem(IMPORTACION_MASIVA_BORRADOR_STORAGE_KEY);
}

export function restoreImportacionMasivaBorradorFilas(): BorradorFila[] | null {
  return readImportacionMasivaBorradorSnapshot()?.filas ?? null;
}
