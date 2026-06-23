import { apiRequest } from '../../../shared/http/client';
import type {
  MyChatAssistantConfiguration,
  SaveMyChatAssistantConfigurationPayload,
} from '../model/myChatAssistantConfiguration';

export async function createMyChatAssistantConfiguration(
  payload: SaveMyChatAssistantConfigurationPayload,
): Promise<MyChatAssistantConfiguration> {
  const response = await apiRequest<MyChatAssistantConfiguration>('/chat-assistant/me/configurations', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  return response.resultado;
}

export async function updateMyChatAssistantConfiguration(
  credentialId: number,
  payload: SaveMyChatAssistantConfigurationPayload,
): Promise<MyChatAssistantConfiguration> {
  const response = await apiRequest<MyChatAssistantConfiguration>(
    `/chat-assistant/me/configurations/${credentialId}`,
    {
      method: 'PUT',
      body: JSON.stringify(payload),
    },
  );

  return response.resultado;
}

export async function deleteMyChatAssistantConfiguration(credentialId: number): Promise<void> {
  await apiRequest<Record<string, never>>(`/chat-assistant/me/configurations/${credentialId}`, {
    method: 'DELETE',
  });
}
