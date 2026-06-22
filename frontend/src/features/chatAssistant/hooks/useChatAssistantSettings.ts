import { useCallback, useEffect, useMemo, useState } from 'react';
import { getMyChatAssistantConfiguration } from '../api/getMyChatAssistantConfiguration';
import { getProviderCatalog } from '../api/getProviderCatalog';
import { saveMyChatAssistantConfiguration } from '../api/saveMyChatAssistantConfiguration';
import { updateMyChatAssistantConfigurationStatus } from '../api/updateMyChatAssistantConfigurationStatus';
import {
  emptyMyChatAssistantConfiguration,
  type MyChatAssistantConfiguration,
} from '../model/myChatAssistantConfiguration';
import {
  findProviderCatalogItem,
  type ChatAssistantProviderCatalogItem,
} from '../model/providerCatalog';
import { resolveChatAssistantSaveValidationErrorKey } from '../utils/validateChatAssistantConfiguration';

type ChatAssistantSettingsFormState = {
  providerId: string | null;
  modelId: string;
  baseUrl: string;
  apiKey: string;
};

export function useChatAssistantSettings() {
  const [catalogItems, setCatalogItems] = useState<ChatAssistantProviderCatalogItem[]>([]);
  const [configuration, setConfiguration] = useState<MyChatAssistantConfiguration>(
    emptyMyChatAssistantConfiguration,
  );
  const [formState, setFormState] = useState<ChatAssistantSettingsFormState>({
    providerId: null,
    modelId: '',
    baseUrl: '',
    apiKey: '',
  });
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [isUpdatingStatus, setIsUpdatingStatus] = useState(false);
  const [loadErrorKey, setLoadErrorKey] = useState<string | null>(null);
  const [saveErrorKey, setSaveErrorKey] = useState<string | null>(null);
  const [statusErrorKey, setStatusErrorKey] = useState<string | null>(null);
  const [saveSuccessVisible, setSaveSuccessVisible] = useState(false);

  const loadData = useCallback(async () => {
    setIsLoading(true);
    setLoadErrorKey(null);

    try {
      const [catalog, currentConfiguration] = await Promise.all([
        getProviderCatalog(),
        getMyChatAssistantConfiguration(),
      ]);

      setCatalogItems(catalog.items);
      setConfiguration(currentConfiguration);
      setFormState({
        providerId: currentConfiguration.providerId || null,
        modelId: currentConfiguration.modelId,
        baseUrl: currentConfiguration.baseUrl,
        apiKey: '',
      });
    } catch {
      setLoadErrorKey('chatAssistant.settings.loadFailed');
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadData();
  }, [loadData]);

  const selectedProvider = useMemo(
    () => findProviderCatalogItem(catalogItems, formState.providerId),
    [catalogItems, formState.providerId],
  );

  const providerInactive = Boolean(
    configuration.hasConfiguration
      && configuration.providerId
      && !catalogItems.some((item) => item.providerId === configuration.providerId),
  );

  const saveConfiguration = useCallback(async () => {
    const validationErrorKey = resolveChatAssistantSaveValidationErrorKey({
      providerId: formState.providerId ?? '',
      modelId: formState.modelId,
      baseUrl: formState.baseUrl,
      apiKey: formState.apiKey,
      requiresBaseUrl: selectedProvider?.requiresBaseUrl ?? false,
      hasExistingApiKey: configuration.hasApiKey,
    });

    if (validationErrorKey) {
      setSaveErrorKey(validationErrorKey);
      setSaveSuccessVisible(false);
      return;
    }

    setIsSaving(true);
    setSaveErrorKey(null);
    setSaveSuccessVisible(false);

    try {
      const payload = {
        providerId: formState.providerId ?? '',
        modelId: formState.modelId.trim(),
        baseUrl: formState.baseUrl.trim(),
        ...(formState.apiKey.trim() ? { apiKey: formState.apiKey.trim() } : {}),
      };

      const response = await saveMyChatAssistantConfiguration(payload);
      setConfiguration(response.resultado);
      setFormState((current) => ({
        ...current,
        providerId: response.resultado.providerId || null,
        modelId: response.resultado.modelId,
        baseUrl: response.resultado.baseUrl,
        apiKey: '',
      }));
      setSaveSuccessVisible(true);
    } catch {
      setSaveErrorKey('chatAssistant.settings.saveFailed');
    } finally {
      setIsSaving(false);
    }
  }, [configuration.hasApiKey, formState, selectedProvider?.requiresBaseUrl]);

  const toggleEnabled = useCallback(async (isEnabled: boolean) => {
    if (!configuration.hasConfiguration) {
      return;
    }

    setIsUpdatingStatus(true);
    setStatusErrorKey(null);

    try {
      const response = await updateMyChatAssistantConfigurationStatus(isEnabled);
      setConfiguration(response.resultado);
    } catch {
      setStatusErrorKey('chatAssistant.settings.statusUpdateFailed');
    } finally {
      setIsUpdatingStatus(false);
    }
  }, [configuration.hasConfiguration]);

  return {
    catalogItems,
    configuration,
    formState,
    isLoading,
    isSaving,
    isUpdatingStatus,
    loadErrorKey,
    saveErrorKey,
    statusErrorKey,
    saveSuccessVisible,
    selectedProvider,
    providerInactive,
    setFormState,
    saveConfiguration,
    toggleEnabled,
  };
}
