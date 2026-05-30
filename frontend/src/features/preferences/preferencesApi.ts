import { apiRequest } from '../../shared/http/client';

export type UserPreferencesPayload = {
  locale: string;
  theme: string;
  openInNewTab: boolean;
};

export async function preferencesRequest(): Promise<{
  error: number;
  respuesta: string;
  resultado: UserPreferencesPayload;
}> {
  return apiRequest<UserPreferencesPayload>('/users/me/preferences');
}

export async function patchOpenInNewTabPreference(openInNewTab: boolean): Promise<{
  error: number;
  respuesta: string;
  resultado: { openInNewTab: boolean };
}> {
  return apiRequest<{ openInNewTab: boolean }>('/users/me/preferences', {
    method: 'PATCH',
    body: JSON.stringify({ openInNewTab }),
  });
}
