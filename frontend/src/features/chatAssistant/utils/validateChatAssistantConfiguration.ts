export type ValidateChatAssistantSaveInput = {
  providerId: string;
  modelId: string;
  baseUrl: string;
  apiKey: string;
  requiresBaseUrl: boolean;
  hasExistingApiKey: boolean;
};

export function resolveChatAssistantSaveValidationErrorKey(
  input: ValidateChatAssistantSaveInput,
): string | null {
  if (!input.providerId.trim()) {
    return 'chatAssistant.settings.providerRequired';
  }

  if (!input.modelId.trim()) {
    return 'chatAssistant.settings.modelIdRequired';
  }

  if (input.requiresBaseUrl && !input.baseUrl.trim()) {
    return 'chatAssistant.settings.baseUrlRequired';
  }

  if (!input.hasExistingApiKey && !input.apiKey.trim()) {
    return 'chatAssistant.settings.apiKeyRequired';
  }

  return null;
}
