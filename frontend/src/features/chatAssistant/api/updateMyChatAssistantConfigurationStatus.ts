import { apiRequest } from '../../../shared/http/client';
import type { MyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';

export async function updateMyChatAssistantConfigurationStatus(
  isEnabled: boolean,
  credentialId: number,
): Promise<{
  error: number;
  respuesta: string;
  resultado: MyChatAssistantConfiguration;
}> {
  return apiRequest<MyChatAssistantConfiguration>(
    `/chat-assistant/me/configurations/${credentialId}/status`,
    {
      method: 'PATCH',
      body: JSON.stringify({ isEnabled }),
    },
  );
}
