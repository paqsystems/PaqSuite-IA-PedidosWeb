import { apiRequest } from '../../../shared/http/client';
import type {
  MyChatAssistantConfiguration,
  SaveMyChatAssistantConfigurationPayload,
} from '../model/myChatAssistantConfiguration';

export async function saveMyChatAssistantConfiguration(
  payload: SaveMyChatAssistantConfigurationPayload,
): Promise<{
  error: number;
  respuesta: string;
  resultado: MyChatAssistantConfiguration;
}> {
  return apiRequest<MyChatAssistantConfiguration>('/chat-assistant/me/configuration', {
    method: 'PUT',
    body: JSON.stringify(payload),
  });
}
