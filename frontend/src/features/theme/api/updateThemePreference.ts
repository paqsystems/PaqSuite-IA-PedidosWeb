import { apiRequest } from '../../../shared/http/client';

export type UpdateThemeResult = {
  theme: string;
};

export async function updateThemePreference(theme: string): Promise<{
  error: number;
  respuesta: string;
  resultado: UpdateThemeResult;
}> {
  return apiRequest<UpdateThemeResult>('/users/me/preferences/theme', {
    method: 'PATCH',
    body: JSON.stringify({ theme }),
  });
}
