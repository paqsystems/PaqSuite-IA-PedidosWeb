import { apiRequest } from '../../../../shared/http/client';

export type AdminRoleItem = {
  id: number;
  nombreRol: string;
  descripcionRol: string;
  accesoTotal: boolean;
  enUso: boolean;
};

export type AdminRolePayload = {
  nombreRol?: string;
  descripcionRol?: string | null;
  accesoTotal?: boolean;
};

export type AdminRoleAttributeItem = {
  procedimiento: string;
  menuText: string;
  menuKey: string;
  permisoAlta: boolean;
  permisoBaja: boolean;
  permisoModi: boolean;
  permisoRepo: boolean;
};

export type AdminRoleAttributesResponse = {
  readOnly: boolean;
  rol: {
    id: number;
    nombreRol: string;
    accesoTotal: boolean;
  };
  items: AdminRoleAttributeItem[];
};

export async function fetchAdminRoles(search?: string): Promise<AdminRoleItem[]> {
  const query = search?.trim() ? `?search=${encodeURIComponent(search.trim())}` : '';
  const response = await apiRequest<{ items: AdminRoleItem[] }>(`/admin/roles${query}`);

  return response.resultado.items;
}

export async function createAdminRole(payload: AdminRolePayload): Promise<AdminRoleItem> {
  const response = await apiRequest<AdminRoleItem>('/admin/roles', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function updateAdminRole(id: number, payload: AdminRolePayload): Promise<AdminRoleItem> {
  const response = await apiRequest<AdminRoleItem>(`/admin/roles/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function deleteAdminRole(id: number): Promise<void> {
  await apiRequest<Record<string, never>>(`/admin/roles/${id}`, {
    method: 'DELETE',
  });
}

export async function fetchRoleAttributes(rolId: number): Promise<AdminRoleAttributesResponse> {
  const response = await apiRequest<AdminRoleAttributesResponse>(`/admin/roles/${rolId}/atributos`);

  return response.resultado;
}

export async function saveRoleAttributes(
  rolId: number,
  items: AdminRoleAttributeItem[],
): Promise<{ actualizados: number; eliminados: number }> {
  const response = await apiRequest<{ actualizados: number; eliminados: number }>(
    `/admin/roles/${rolId}/atributos`,
    {
      method: 'PUT',
      body: JSON.stringify({ items }),
    },
  );

  return response.resultado;
}
