import { useCallback, useEffect, useMemo, useState } from 'react';
import { getMyChatAssistantConfigurations } from '../api/getMyChatAssistantConfigurations';
import {
  createMyChatAssistantConfiguration,
  deleteMyChatAssistantConfiguration,
  updateMyChatAssistantConfiguration,
} from '../api/manageMyChatAssistantConfigurations';
import { getProviderCatalog } from '../api/getProviderCatalog';
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

export type ChatAssistantConfigurationFormState = {
  displayName: string;
  providerId: string | null;
  modelId: string;
  baseUrl: string;
  apiKey: string;
};

const emptyFormState: ChatAssistantConfigurationFormState = {
  displayName: '',
  providerId: null,
  modelId: '',
  baseUrl: '',
  apiKey: '',
};

export function useChatAssistantConfigurations() {
  const [catalogItems, setCatalogItems] = useState<ChatAssistantProviderCatalogItem[]>([]);
  const [configurations, setConfigurations] = useState<MyChatAssistantConfiguration[]>([]);
  const [formState, setFormState] = useState<ChatAssistantConfigurationFormState>(emptyFormState);
  const [editingCredentialId, setEditingCredentialId] = useState<number | null>(null);
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [loadErrorKey, setLoadErrorKey] = useState<string | null>(null);
  const [saveErrorKey, setSaveErrorKey] = useState<string | null>(null);
  const [deleteErrorKey, setDeleteErrorKey] = useState<string | null>(null);
  const [saveSuccessVisible, setSaveSuccessVisible] = useState(false);

  const loadData = useCallback(async () => {
    setIsLoading(true);
    setLoadErrorKey(null);

    try {
      const [catalog, configurationList] = await Promise.all([
        getProviderCatalog(),
        getMyChatAssistantConfigurations(),
      ]);

      setCatalogItems(catalog.items);
      setConfigurations(configurationList.items);
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

  const editingConfiguration = useMemo(
    () =>
      editingCredentialId === null
        ? null
        : configurations.find((item) => item.credentialId === editingCredentialId) ?? null,
    [configurations, editingCredentialId],
  );

  const openCreateForm = useCallback(() => {
    setEditingCredentialId(null);
    setFormState(emptyFormState);
    setSaveErrorKey(null);
    setSaveSuccessVisible(false);
    setIsFormOpen(true);
  }, []);

  const openEditForm = useCallback((configuration: MyChatAssistantConfiguration) => {
    setEditingCredentialId(configuration.credentialId);
    setFormState({
      displayName: configuration.displayName,
      providerId: configuration.providerId || null,
      modelId: configuration.modelId,
      baseUrl: configuration.baseUrl,
      apiKey: '',
    });
    setSaveErrorKey(null);
    setSaveSuccessVisible(false);
    setIsFormOpen(true);
  }, []);

  const closeForm = useCallback(() => {
    setIsFormOpen(false);
    setEditingCredentialId(null);
    setFormState(emptyFormState);
    setSaveErrorKey(null);
  }, []);

  const saveConfiguration = useCallback(async () => {
    const validationErrorKey = resolveChatAssistantSaveValidationErrorKey({
      displayName: formState.displayName,
      providerId: formState.providerId ?? '',
      modelId: formState.modelId,
      baseUrl: formState.baseUrl,
      apiKey: formState.apiKey,
      requiresBaseUrl: selectedProvider?.requiresBaseUrl ?? false,
      hasExistingApiKey: editingConfiguration?.hasApiKey ?? false,
    });

    if (validationErrorKey) {
      setSaveErrorKey(validationErrorKey);
      setSaveSuccessVisible(false);
      return false;
    }

    setIsSaving(true);
    setSaveErrorKey(null);
    setSaveSuccessVisible(false);

    try {
      const payload = {
        displayName: formState.displayName.trim(),
        providerId: formState.providerId ?? '',
        modelId: formState.modelId.trim(),
        baseUrl: formState.baseUrl.trim(),
        ...(formState.apiKey.trim() ? { apiKey: formState.apiKey.trim() } : {}),
      };

      if (editingCredentialId !== null) {
        await updateMyChatAssistantConfiguration(editingCredentialId, payload);
      } else {
        await createMyChatAssistantConfiguration(payload);
      }

      await loadData();
      setSaveSuccessVisible(true);
      setIsFormOpen(false);
      setEditingCredentialId(null);
      setFormState(emptyFormState);

      return true;
    } catch {
      setSaveErrorKey('chatAssistant.settings.saveFailed');
      return false;
    } finally {
      setIsSaving(false);
    }
  }, [editingConfiguration?.hasApiKey, editingCredentialId, formState, loadData, selectedProvider?.requiresBaseUrl]);

  const deleteConfiguration = useCallback(
    async (credentialId: number) => {
      setIsDeleting(true);
      setDeleteErrorKey(null);

      try {
        await deleteMyChatAssistantConfiguration(credentialId);
        await loadData();
        return true;
      } catch {
        setDeleteErrorKey('chatAssistant.settings.deleteFailed');
        return false;
      } finally {
        setIsDeleting(false);
      }
    },
    [loadData],
  );

  const toggleEnabled = useCallback(
    async (configuration: MyChatAssistantConfiguration, isEnabled: boolean) => {
      try {
        await updateMyChatAssistantConfigurationStatus(isEnabled, configuration.credentialId);
        await loadData();
        return true;
      } catch {
        setSaveErrorKey('chatAssistant.settings.statusUpdateFailed');
        return false;
      }
    },
    [loadData],
  );

  return {
    catalogItems,
    configurations,
    editingConfiguration: editingConfiguration ?? emptyMyChatAssistantConfiguration,
    formState,
    isFormOpen,
    isLoading,
    isSaving,
    isDeleting,
    loadErrorKey,
    saveErrorKey,
    deleteErrorKey,
    saveSuccessVisible,
    selectedProvider,
    setFormState,
    openCreateForm,
    openEditForm,
    closeForm,
    saveConfiguration,
    deleteConfiguration,
    toggleEnabled,
  };
}
