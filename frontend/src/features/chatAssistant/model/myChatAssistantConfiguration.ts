export type MyChatAssistantConfiguration = {
  hasConfiguration: boolean;
  hasApiKey: boolean;
  apiKeyHint: string;
  providerId: string;
  modelId: string;
  baseUrl: string;
  supportsVision: boolean;
  isEnabled: boolean;
};

export type SaveMyChatAssistantConfigurationPayload = {
  providerId: string;
  modelId: string;
  baseUrl: string;
  apiKey?: string;
};

export const emptyMyChatAssistantConfiguration: MyChatAssistantConfiguration = {
  hasConfiguration: false,
  hasApiKey: false,
  apiKeyHint: '',
  providerId: '',
  modelId: '',
  baseUrl: '',
  supportsVision: false,
  isEnabled: false,
};
