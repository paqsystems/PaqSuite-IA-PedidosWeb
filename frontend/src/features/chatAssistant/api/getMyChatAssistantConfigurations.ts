import { apiRequest } from '../../../shared/http/client';
import type { MyChatAssistantConfigurationsResult } from '../model/myChatAssistantConfiguration';

export async function getMyChatAssistantConfigurations(): Promise<MyChatAssistantConfigurationsResult> {
  const response = await apiRequest<MyChatAssistantConfigurationsResult>(
    '/chat-assistant/me/configurations',
  );

  return response.resultado;
}
