import type { TFunction } from 'i18next';
import { grabarComprobante } from '../../api/comprobanteApi';
import { resolveGrabacionErrorMessages } from '../../utils/resolveGrabacionErrorMessages';
import type {
  BorradorFila,
  ImportacionMasivaGrabacionResumen,
  ImportacionMasivaProgreso,
} from '../types/importacionMasivaTypes';
import { mapBorradorToGrabarPayload } from './mapBorradorToGrabarPayload';

export type GrabarLoteSecuencialCallbacks = {
  onProgreso: (progreso: ImportacionMasivaProgreso | null) => void;
  onFilaOk: (idInterno: string) => void;
  onFilaError: (idInterno: string, errorMessage: string) => void;
};

export async function grabarLoteSecuencial(
  filas: BorradorFila[],
  t: TFunction,
  callbacks: GrabarLoteSecuencialCallbacks,
): Promise<ImportacionMasivaGrabacionResumen> {
  const snapshot = [...filas];
  const n = snapshot.length;
  let ok = 0;
  let err = 0;

  callbacks.onProgreso({ x: 0, n });

  for (let index = 0; index < snapshot.length; index += 1) {
    const fila = snapshot[index];
    callbacks.onProgreso({ x: index + 1, n });

    try {
      await grabarComprobante(mapBorradorToGrabarPayload(fila));
      callbacks.onFilaOk(fila.idInterno);
      ok += 1;
    } catch (error) {
      const messages = resolveGrabacionErrorMessages(error, t);
      const errorMessage = messages.join('; ') || t('pedidos.importacionMasiva.errorGrabacionGenerico');
      callbacks.onFilaError(fila.idInterno, errorMessage);
      err += 1;
    }
  }

  callbacks.onProgreso(null);

  return { ok, err };
}
