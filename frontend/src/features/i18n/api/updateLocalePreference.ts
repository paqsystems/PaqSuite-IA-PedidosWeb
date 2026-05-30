import { apiRequest } from '../../../shared/http/client';

export type UpdateLocaleResult = {
  locale: string;
};

export async function updateLocalePreference(locale: string): Promise<{
  error: number;
  respuesta: string;
  resultado: UpdateLocaleResult;
}> {
  return apiRequest<UpdateLocaleResult>('/users/me/preferences/locale', {
    method: 'PATCH',
    body: JSON.stringify({ locale }),
  });
}
