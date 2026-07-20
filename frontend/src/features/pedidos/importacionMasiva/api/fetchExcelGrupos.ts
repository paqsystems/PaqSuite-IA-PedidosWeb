import { getApiBaseUrl, buildTenantHeaders, ApiClientError } from '../../../../shared/http/client';
import { dispatchAuthExpired } from '../../../auth/authEvents';
import type { ImportacionMasivaGrupoApi } from '../types/importacionMasivaTypes';

type Envelope<T> = {
  error: number;
  respuesta: string;
  resultado: T;
};

async function authHeaders(): Promise<Headers> {
  const headers = new Headers(buildTenantHeaders());
  const token = localStorage.getItem('pedidosweb.auth.token');
  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }
  headers.set('Content-Type', 'application/json');

  return headers;
}

export async function fetchExcelGrupos(guidImportacion: string): Promise<ImportacionMasivaGrupoApi[]> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/filas/validas`,
    { headers: await authHeaders() },
  );

  let payload: Envelope<{ grupos?: ImportacionMasivaGrupoApi[] }>;
  try {
    payload = (await response.json()) as Envelope<{ grupos?: ImportacionMasivaGrupoApi[] }>;
  } catch {
    throw new ApiClientError(response.status, 'request.failed', response.status);
  }

  if (!response.ok) {
    if (response.status === 401) {
      dispatchAuthExpired(payload.respuesta ?? 'auth.unauthenticated');
    }
    throw new ApiClientError(response.status, payload.respuesta ?? 'request.failed', payload.error ?? response.status);
  }

  return payload.resultado.grupos ?? [];
}
