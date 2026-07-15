import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import TextBox from 'devextreme-react/text-box';
import { SelectBoxDx } from '../../../shared/ui/controls/SelectBoxDx';
import { isDevExtremeUserChange } from '../../../shared/ui/devextremeUserChange';
import {
  findProviderCatalogItem,
  resolveProviderSuggestedModels,
  type ChatAssistantProviderCatalogItem,
} from '../../chatAssistant/model/providerCatalog';
import './ChatAssistantModelSelect.css';

type ChatAssistantModelSelectProps = {
  providerId: string | null;
  catalogItems: ChatAssistantProviderCatalogItem[];
  modelId: string;
  disabled?: boolean;
  onModelIdChange: (modelId: string) => void;
};

export function ChatAssistantModelSelect({
  providerId,
  catalogItems,
  modelId,
  disabled = false,
  onModelIdChange,
}: ChatAssistantModelSelectProps) {
  const { t } = useTranslation();
  const selectedProvider = useMemo(
    () => findProviderCatalogItem(catalogItems, providerId),
    [catalogItems, providerId],
  );
  const suggestedModels = useMemo(
    () => resolveProviderSuggestedModels(selectedProvider),
    [selectedProvider],
  );
  const modelOptions = useMemo(() => {
    const options = suggestedModels.map((model) => ({ modelId: model }));

    if (modelId && !suggestedModels.includes(modelId)) {
      return [...options, { modelId }];
    }

    return options;
  }, [modelId, suggestedModels]);

  if (suggestedModels.length === 0) {
    return (
      <label className="chatAssistantModelSelect__field">
        <span>{t('chatAssistant.settings.modelIdLabel')}</span>
        <TextBox
          value={modelId}
          disabled={disabled}
          inputAttr={{
            'data-testid': 'chatAssistantConfigurationModelIdInput',
            autoComplete: 'off',
            name: 'chatAssistantModelId',
          }}
          onValueChanged={(event) => {
            if (!isDevExtremeUserChange(event)) {
              return;
            }

            onModelIdChange(String(event.value ?? ''));
          }}
        />
      </label>
    );
  }

  return (
    <label className="chatAssistantModelSelect__field">
      <span>{t('chatAssistant.settings.modelIdLabel')}</span>
      <SelectBoxDx
        dataSource={modelOptions}
        displayExpr="modelId"
        valueExpr="modelId"
        value={modelId || null}
        disabled={disabled}
        searchEnabled
        acceptCustomValue
        showClearButton
        inputAttr={{
          'data-testid': 'chatAssistantConfigurationModelIdInput',
          autoComplete: 'off',
          name: 'chatAssistantModelId',
        }}
        elementAttr={{ 'data-testid': 'chatAssistantConfigurationModelSelect' }}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          onModelIdChange(String(event.value ?? ''));
        }}
        onCustomItemCreating={(event) => {
          const customModelId = event.text?.trim() ?? '';

          if (!customModelId) {
            event.customItem = modelId ? { modelId } : null;
            return;
          }

          event.customItem = { modelId: customModelId };
        }}
      />
      <p className="chatAssistantModelSelect__hint" data-testid="chatAssistantModelSelectHint">
        {t('chatAssistant.settings.modelSelectHint')}
      </p>
    </label>
  );
}
