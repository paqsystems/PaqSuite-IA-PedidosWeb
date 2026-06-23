import type { MyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';

export function isChatAssistantConfigurationOperational(
  configuration: MyChatAssistantConfiguration,
  activeProviderIds: string[],
): boolean {
  return (
    configuration.hasConfiguration
    && configuration.credentialId > 0
    && configuration.hasApiKey
    && configuration.isEnabled
    && configuration.providerId.trim() !== ''
    && activeProviderIds.includes(configuration.providerId)
  );
}

export function resolveOperationalConfigurations(
  configurations: MyChatAssistantConfiguration[],
  activeProviderIds: string[],
): MyChatAssistantConfiguration[] {
  return configurations.filter((configuration) =>
    isChatAssistantConfigurationOperational(configuration, activeProviderIds),
  );
}

export const chatAssistantSelectedCredentialStorageKey = 'chatAssistantSelectedCredentialId';

export function readStoredCredentialId(): number | null {
  const rawValue = sessionStorage.getItem(chatAssistantSelectedCredentialStorageKey);

  if (!rawValue) {
    return null;
  }

  const parsed = Number.parseInt(rawValue, 10);

  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
}

export function storeCredentialId(credentialId: number): void {
  sessionStorage.setItem(chatAssistantSelectedCredentialStorageKey, String(credentialId));
}

export function resolveSelectedOperationalConfiguration(
  configurations: MyChatAssistantConfiguration[],
  activeProviderIds: string[],
  preferredCredentialId: number | null,
): MyChatAssistantConfiguration | null {
  const operationalConfigurations = resolveOperationalConfigurations(configurations, activeProviderIds);

  if (operationalConfigurations.length === 0) {
    return null;
  }

  if (preferredCredentialId !== null) {
    const preferred = operationalConfigurations.find(
      (configuration) => configuration.credentialId === preferredCredentialId,
    );

    if (preferred) {
      return preferred;
    }
  }

  return operationalConfigurations[0] ?? null;
}
