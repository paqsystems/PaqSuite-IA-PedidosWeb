import { useCallback, useEffect, useMemo, useState } from 'react';
import { getMyChatAssistantConfigurations } from '../../../chatAssistant/api/getMyChatAssistantConfigurations';
import { getProviderCatalog } from '../../../chatAssistant/api/getProviderCatalog';
import type { MyChatAssistantConfiguration } from '../../../chatAssistant/model/myChatAssistantConfiguration';
import {
  readStoredCredentialId,
  resolveOperationalConfigurations,
  resolveSelectedOperationalConfiguration,
  storeCredentialId,
} from '../../../chatAssistant/utils/resolveChatAssistantOperationalConfiguration';

export function useCargaAsistenteLlmCredential() {
  const [configurations, setConfigurations] = useState<MyChatAssistantConfiguration[]>([]);
  const [selectedCredentialId, setSelectedCredentialId] = useState<number | null>(null);
  const [activeProviderIds, setActiveProviderIds] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  const loadState = useCallback(async () => {
    setIsLoading(true);

    try {
      const [catalog, configurationList] = await Promise.all([
        getProviderCatalog(),
        getMyChatAssistantConfigurations(),
      ]);

      const providerIds = catalog.items.map((item) => item.providerId);
      setActiveProviderIds(providerIds);
      setConfigurations(configurationList.items);

      const preferredCredentialId = readStoredCredentialId();
      const selectedConfiguration = resolveSelectedOperationalConfiguration(
        configurationList.items,
        providerIds,
        preferredCredentialId,
      );

      setSelectedCredentialId(selectedConfiguration?.credentialId ?? null);
    } catch {
      setActiveProviderIds([]);
      setConfigurations([]);
      setSelectedCredentialId(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadState();
  }, [loadState]);

  const operationalConfigurations = useMemo(
    () => resolveOperationalConfigurations(configurations, activeProviderIds),
    [activeProviderIds, configurations],
  );

  const selectedConfiguration = useMemo(
    () =>
      resolveSelectedOperationalConfiguration(
        configurations,
        activeProviderIds,
        selectedCredentialId,
      ),
    [activeProviderIds, configurations, selectedCredentialId],
  );

  const selectCredential = useCallback((credentialId: number | null) => {
    setSelectedCredentialId(credentialId);

    if (credentialId !== null) {
      storeCredentialId(credentialId);
    }
  }, []);

  return {
    isLoading,
    operationalConfigurations,
    selectedConfiguration,
    selectedCredentialId,
    selectCredential,
    reload: loadState,
  };
}
