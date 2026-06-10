import { apiRequest } from '../../shared/http/client';
import type { ApiEnvelope, SessionContext } from './types';

export type ChangePasswordPayload = {
  currentPassword: string;
  newPassword: string;
  newPasswordConfirmation: string;
};

export async function changePasswordRequest(
  payload: ChangePasswordPayload,
): Promise<ApiEnvelope<SessionContext>> {
  return apiRequest<SessionContext>('/auth/password/change', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}
