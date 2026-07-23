import { apiRequest } from '../../../../shared/http/client';
import type {
  CargaAsistenteDraftContext,
  CargaAsistenteImagePayload,
  CargaAsistenteModality,
  CargaAsistentePendingChoice,
  CargaAsistenteTurnResult,
} from '../model/cargaAsistenteTypes';

export type PostCargaAsistenteTurnPayload = {
  message: string;
  modality: CargaAsistenteModality;
  draftContext: CargaAsistenteDraftContext;
  pendingChoice?: CargaAsistentePendingChoice;
  credentialId?: number | null;
  images?: CargaAsistenteImagePayload[];
};

export async function postCargaAsistenteTurn(
  payload: PostCargaAsistenteTurnPayload,
): Promise<CargaAsistenteTurnResult> {
  const response = await apiRequest<CargaAsistenteTurnResult>('/pedidos/carga/asistente/turn', {
    method: 'POST',
    body: JSON.stringify({
      message: payload.message,
      modality: payload.modality,
      draftContext: payload.draftContext,
      ...(payload.pendingChoice ? { pendingChoice: payload.pendingChoice } : {}),
      ...(payload.credentialId ? { credentialId: payload.credentialId } : {}),
      ...(payload.images && payload.images.length > 0 ? { images: payload.images } : {}),
    }),
  });

  return response.resultado;
}
