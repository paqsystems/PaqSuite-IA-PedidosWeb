import { apiRequest } from '../../../shared/http/client';
import type { MyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';

export async function getMyChatAssistantConfiguration(): Promise<MyChatAssistantConfiguration> {
  const response = await apiRequest<MyChatAssistantConfiguration>('/chat-assistant/me/configuration');
  return response.resultado;
}
