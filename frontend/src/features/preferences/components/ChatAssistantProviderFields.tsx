import TextBox from 'devextreme-react/text-box';
import { useTranslation } from 'react-i18next';
import type { ChatAssistantProviderCatalogItem } from '../../chatAssistant/model/providerCatalog';

type ChatAssistantProviderFieldsProps = {
  provider: ChatAssistantProviderCatalogItem | null;
  baseUrl: string;
  onBaseUrlChange: (value: string) => void;
};

export function ChatAssistantProviderFields({
  provider,
  baseUrl,
  onBaseUrlChange,
}: ChatAssistantProviderFieldsProps) {
  const { t } = useTranslation();

  if (!provider?.requiresBaseUrl) {
    return null;
  }

  return (
    <label className="chatAssistantSettingsSection__field">
      <span>{t('chatAssistant.settings.baseUrlLabel')}</span>
      <TextBox
        value={baseUrl}
        inputAttr={{ 'data-testid': 'chatAssistantConfigurationBaseUrlInput' }}
        onValueChanged={(event) => {
          onBaseUrlChange(String(event.value ?? ''));
        }}
      />
      <p
        className="chatAssistantProviderFields__hint"
        data-testid="chatAssistantProviderRequiresBaseUrlHint"
      >
        {t('chatAssistant.settings.requiresBaseUrlHint')}
      </p>
    </label>
  );
}
