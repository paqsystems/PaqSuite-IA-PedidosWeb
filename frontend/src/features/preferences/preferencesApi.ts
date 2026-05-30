import { apiRequest } from '../../shared/http/client';

export type UserPreferencesPayload = {
  locale: string;
  theme: string;
};

export async function preferencesRequest(): Promise<{
  error: number;
  respuesta: string;
  resultado: UserPreferencesPayload;
}> {
  return apiRequest<UserPreferencesPayload>('/users/me/preferences');
}
