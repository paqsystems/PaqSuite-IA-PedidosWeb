import { apiRequest, ApiClientError } from '../../../shared/http/client';
import type { GridLayoutActive, GridLayoutListItem } from '../model/gridLayoutTypes';

export async function fetchPublicConfig(): Promise<{ gridLayoutsEnabled: boolean }> {
  const response = await apiRequest<{ gridLayoutsEnabled: boolean }>('/config/public');

  return response.resultado;
}

export async function fetchGridLayouts(proceso: string, gridId: string): Promise<GridLayoutListItem[]> {
  const query = new URLSearchParams({ proceso, gridId });
  const response = await apiRequest<{ items: GridLayoutListItem[] }>(`/grid-layouts?${query.toString()}`);

  return response.resultado.items ?? [];
}

export async function fetchActiveGridLayout(proceso: string, gridId: string): Promise<GridLayoutActive> {
  const query = new URLSearchParams({ proceso, gridId });
  const response = await apiRequest<GridLayoutActive>(`/grid-layouts/active?${query.toString()}`);

  return response.resultado;
}

export async function createGridLayout(payload: {
  proceso: string;
  gridId: string;
  layoutName: string;
  stateJson: Record<string, unknown>;
}): Promise<GridLayoutActive> {
  const response = await apiRequest<GridLayoutActive>('/grid-layouts', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function updateGridLayout(
  layoutId: number,
  stateJson: Record<string, unknown>,
): Promise<GridLayoutActive> {
  const response = await apiRequest<GridLayoutActive>(`/grid-layouts/${layoutId}`, {
    method: 'PUT',
    body: JSON.stringify({ stateJson }),
  });

  return response.resultado;
}

export async function deleteGridLayout(layoutId: number): Promise<void> {
  await apiRequest(`/grid-layouts/${layoutId}`, {
    method: 'DELETE',
  });
}

export async function setActiveGridLayout(payload: {
  proceso: string;
  gridId: string;
  layoutId: number | null;
}): Promise<void> {
  await apiRequest('/grid-layouts/active', {
    method: 'PUT',
    body: JSON.stringify(payload),
  });
}

export function isGridLayoutDuplicateError(error: unknown): boolean {
  return error instanceof ApiClientError && error.errorCode === 2001;
}
