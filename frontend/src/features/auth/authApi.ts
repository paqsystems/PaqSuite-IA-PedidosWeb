import { apiRequest } from '../../shared/http/client';
import type { ApiEnvelope, LoginPayload, SessionContext } from './types';

export async function loginRequest(payload: LoginPayload): Promise<ApiEnvelope<SessionContext & { token: string }>> {
  return apiRequest<SessionContext & { token: string }>('/auth/login', {
    method: 'POST',
    body: JSON.stringify(payload),
    skipAuth: true,
  });
}

export async function logoutRequest(): Promise<ApiEnvelope<Record<string, never>>> {
  return apiRequest<Record<string, never>>('/auth/logout', {
    method: 'POST',
  });
}

export async function meRequest(): Promise<ApiEnvelope<SessionContext>> {
  return apiRequest<SessionContext>('/auth/me');
}
