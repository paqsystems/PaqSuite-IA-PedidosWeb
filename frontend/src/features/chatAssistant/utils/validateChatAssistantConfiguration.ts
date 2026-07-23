export type ValidateChatAssistantSaveInput = {
  displayName: string;
  providerId: string;
  modelId: string;
  baseUrl: string;
  apiKey: string;
  requiresBaseUrl: boolean;
  hasExistingApiKey: boolean;
};

const providersRequiringApiKeyPrefix = new Set(['openai', 'openRouter', 'groq', 'anthropic']);

function apiKeyMatchesProviderFormat(providerId: string, apiKey: string): boolean {
  if (providerId === 'groq') {
    return apiKey.startsWith('gsk_') || apiKey.startsWith('sk-');
  }

  if (providerId === 'anthropic') {
    return apiKey.startsWith('sk-ant-') || apiKey.startsWith('sk-');
  }

  if (providerId === 'openai' || providerId === 'openRouter') {
    return apiKey.startsWith('sk-');
  }

  return true;
}

export function resolveChatAssistantSaveValidationErrorKey(
  input: ValidateChatAssistantSaveInput,
): string | null {
  if (!input.displayName.trim()) {
    return 'chatAssistant.settings.displayNameRequired';
  }

  if (!input.providerId.trim()) {
    return 'chatAssistant.settings.providerRequired';
  }

  if (!input.modelId.trim()) {
    return 'chatAssistant.settings.modelIdRequired';
  }

  if (input.requiresBaseUrl && !input.baseUrl.trim()) {
    return 'chatAssistant.settings.baseUrlRequired';
  }

  const apiKey = input.apiKey.trim();

  if (!input.hasExistingApiKey && !apiKey) {
    return 'chatAssistant.settings.apiKeyRequired';
  }

  if (
    apiKey
    && providersRequiringApiKeyPrefix.has(input.providerId)
    && !apiKeyMatchesProviderFormat(input.providerId, apiKey)
  ) {
    return 'chatAssistant.settings.apiKeyInvalidFormat';
  }

  return null;
}
