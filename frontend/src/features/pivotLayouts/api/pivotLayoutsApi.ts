import { fetchPublicConfig as fetchSharedPublicConfig } from '../../config/api/publicConfigApi';
import { apiRequest, ApiClientError } from '../../../shared/http/client';
import type { PivotLayoutActive, PivotLayoutListItem } from '../model/pivotLayoutTypes';
import type { PivotLayoutConfigurationJson } from '../model/pivotLayoutTypes';

export async function fetchPublicConfig() {
  return fetchSharedPublicConfig();
}

export async function fetchPivotLayouts(consultaId: string): Promise<PivotLayoutListItem[]> {
  const query = new URLSearchParams({ consultaId });
  const response = await apiRequest<{ items: PivotLayoutListItem[] }>(`/pivot-configs?${query.toString()}`);

  return response.resultado.items ?? [];
}

export async function fetchActivePivotLayout(consultaId: string): Promise<PivotLayoutActive> {
  const query = new URLSearchParams({ consultaId });
  const response = await apiRequest<PivotLayoutActive>(`/pivot-configs/active?${query.toString()}`);

  return response.resultado;
}

export async function createPivotLayout(payload: {
  consultaId: string;
  nombre: string;
  configuracionJson: PivotLayoutConfigurationJson;
}): Promise<PivotLayoutActive> {
  const response = await apiRequest<PivotLayoutActive>('/pivot-configs', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function updatePivotLayout(
  configId: number,
  configuracionJson: PivotLayoutConfigurationJson,
): Promise<PivotLayoutActive> {
  const response = await apiRequest<PivotLayoutActive>(`/pivot-configs/${configId}`, {
    method: 'PUT',
    body: JSON.stringify({ configuracionJson }),
  });

  return response.resultado;
}

export async function deletePivotLayout(configId: number): Promise<void> {
  await apiRequest(`/pivot-configs/${configId}`, {
    method: 'DELETE',
  });
}

export async function setActivePivotLayout(payload: {
  consultaId: string;
  configId: number | null;
}): Promise<void> {
  await apiRequest('/pivot-configs/active', {
    method: 'PUT',
    body: JSON.stringify(payload),
  });
}

export function isPivotLayoutDuplicateError(error: unknown): boolean {
  return error instanceof ApiClientError && error.errorCode === 2001;
}
