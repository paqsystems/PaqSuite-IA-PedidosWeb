import { apiRequest } from '../../../shared/http/client';
import type { ChatAssistantProviderCatalogResult } from '../model/providerCatalog';

export async function getProviderCatalog(): Promise<ChatAssistantProviderCatalogResult> {
  const response = await apiRequest<ChatAssistantProviderCatalogResult>('/chat-assistant/providers');
  return response.resultado;
}
