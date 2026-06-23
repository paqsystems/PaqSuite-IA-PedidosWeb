import { apiRequest } from '../../../../shared/http/client';

export type AdminPermisoItem = {
  id: number;
  idUsuario: number;
  usuarioCodigo: string;
  usuarioNombre: string;
  idRol: number;
  rolNombre: string;
};

export type AdminUsuarioLookupItem = {
  id: number;
  codigo: string;
  nameUser: string;
};

export type AdminRoleLookupItem = {
  id: number;
  nombreRol: string;
  descripcionRol: string;
  accesoTotal: boolean;
  enUso: boolean;
};

export type AdminPermisoBatchResult = {
  creados: number;
  omitidos: number;
};

export async function fetchAdminPermisos(usuarioId?: number | null, rolId?: number | null): Promise<AdminPermisoItem[]> {
  const params = new URLSearchParams();

  if (usuarioId != null) {
    params.set('usuarioId', String(usuarioId));
  }

  if (rolId != null) {
    params.set('rolId', String(rolId));
  }

  const query = params.toString();
  const response = await apiRequest<{ items: AdminPermisoItem[] }>(
    `/admin/permisos${query ? `?${query}` : ''}`,
  );

  return response.resultado.items;
}

export async function createAdminPermiso(payload: { idUsuario: number; idRol: number }): Promise<AdminPermisoItem> {
  const response = await apiRequest<AdminPermisoItem>('/admin/permisos', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function updateAdminPermiso(id: number, payload: { idRol: number }): Promise<AdminPermisoItem> {
  const response = await apiRequest<AdminPermisoItem>(`/admin/permisos/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function deleteAdminPermiso(id: number): Promise<void> {
  await apiRequest<Record<string, never>>(`/admin/permisos/${id}`, {
    method: 'DELETE',
  });
}

export async function lookupAdminUsuarios(
  search?: string,
  page = 1,
  pageSize = 20,
): Promise<{ items: AdminUsuarioLookupItem[]; total: number }> {
  const params = new URLSearchParams({
    page: String(page),
    pageSize: String(pageSize),
  });

  if (search?.trim()) {
    params.set('search', search.trim());
  }

  const response = await apiRequest<{
    items: AdminUsuarioLookupItem[];
    total: number;
  }>(`/admin/usuarios?${params.toString()}`);

  return {
    items: response.resultado.items,
    total: response.resultado.total,
  };
}

export async function fetchAdminRolesLookup(): Promise<AdminRoleLookupItem[]> {
  const response = await apiRequest<{ items: AdminRoleLookupItem[] }>('/admin/roles');

  return response.resultado.items;
}

export async function createAdminPermisoBatch(payload: {
  mode: 'by_user' | 'by_role';
  anchorId: number;
  rolIds?: number[];
  usuarioIds?: number[];
}): Promise<AdminPermisoBatchResult> {
  const response = await apiRequest<AdminPermisoBatchResult>('/admin/permisos/batch', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}
