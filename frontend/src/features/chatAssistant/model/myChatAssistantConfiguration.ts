export type MyChatAssistantConfiguration = {
  credentialId: number;
  displayName: string;
  hasConfiguration: boolean;
  hasApiKey: boolean;
  apiKeyHint: string;
  providerId: string;
  modelId: string;
  baseUrl: string;
  supportsVision: boolean;
  isEnabled: boolean;
};

export type MyChatAssistantConfigurationsResult = {
  items: MyChatAssistantConfiguration[];
};

export type SaveMyChatAssistantConfigurationPayload = {
  displayName: string;
  providerId: string;
  modelId: string;
  baseUrl: string;
  apiKey?: string;
};

export const emptyMyChatAssistantConfiguration: MyChatAssistantConfiguration = {
  credentialId: 0,
  displayName: '',
  hasConfiguration: false,
  hasApiKey: false,
  apiKeyHint: '',
  providerId: '',
  modelId: '',
  baseUrl: '',
  supportsVision: false,
  isEnabled: false,
};
