import type { MyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';

export function isChatAssistantConfigurationOperational(
  configuration: MyChatAssistantConfiguration,
  activeProviderIds: string[],
): boolean {
  return (
    configuration.hasConfiguration
    && configuration.hasApiKey
    && configuration.isEnabled
    && configuration.providerId.trim() !== ''
    && activeProviderIds.includes(configuration.providerId)
  );
}
