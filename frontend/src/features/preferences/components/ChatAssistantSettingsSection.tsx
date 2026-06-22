import Button from 'devextreme-react/button';
import CheckBox from 'devextreme-react/check-box';
import TextBox from 'devextreme-react/text-box';
import { useTranslation } from 'react-i18next';
import { useChatAssistantSettings } from '../../chatAssistant/hooks/useChatAssistantSettings';
import { SelectBoxDx } from '../../../shared/ui/controls/SelectBoxDx';
import { ChatAssistantProviderFields } from './ChatAssistantProviderFields';
import { ChatAssistantProviderHelpLink } from './ChatAssistantProviderHelpLink';
import './ChatAssistantSettingsSection.css';

export function ChatAssistantSettingsSection() {
  const { t } = useTranslation();
  const {
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
  } = useChatAssistantSettings();

  return (
    <section className="chatAssistantSettingsSection" data-testid="chatAssistantSettingsSection">
      <header className="chatAssistantSettingsSection__header">
        <h2>{t('chatAssistant.settings.title')}</h2>
        <p>{t('chatAssistant.settings.intro')}</p>
      </header>

      {loadErrorKey && (
        <p className="chatAssistantSettingsSection__error" data-testid="chatAssistantSettingsLoadError">
          {t(loadErrorKey)}
        </p>
      )}

      {providerInactive && (
        <p className="chatAssistantSettingsSection__warning" data-testid="chatAssistantProviderInactiveWarning">
          {t('chatAssistant.settings.providerInactiveWarning')}
        </p>
      )}

      <label className="chatAssistantSettingsSection__field">
        <span>{t('chatAssistant.settings.providerLabel')}</span>
        <SelectBoxDx
          dataSource={catalogItems}
          displayExpr="displayName"
          valueExpr="providerId"
          value={formState.providerId}
          isLoading={isLoading}
          disabled={isLoading || catalogItems.length === 0}
          searchEnabled
          showClearButton
          inputAttr={{ 'data-testid': 'chatAssistantConfigurationProviderSelect' }}
          elementAttr={{ 'data-testid': 'chatAssistantProviderSelectBox' }}
          onValueChanged={(event) => {
            setFormState((current) => ({
              ...current,
              providerId: (event.value as string | null) ?? null,
            }));
          }}
        />
      </label>

      <ChatAssistantProviderHelpLink provider={selectedProvider} />

      <label className="chatAssistantSettingsSection__field">
        <span>{t('chatAssistant.settings.modelIdLabel')}</span>
        <TextBox
          value={formState.modelId}
          disabled={isLoading}
          inputAttr={{ 'data-testid': 'chatAssistantConfigurationModelIdInput' }}
          onValueChanged={(event) => {
            setFormState((current) => ({
              ...current,
              modelId: String(event.value ?? ''),
            }));
          }}
        />
      </label>

      <ChatAssistantProviderFields
        provider={selectedProvider}
        baseUrl={formState.baseUrl}
        onBaseUrlChange={(baseUrl) => {
          setFormState((current) => ({
            ...current,
            baseUrl,
          }));
        }}
      />

      <label className="chatAssistantSettingsSection__field">
        <span>{t('chatAssistant.settings.apiKeyLabel')}</span>
        <TextBox
          mode="password"
          value={formState.apiKey}
          disabled={isLoading}
          placeholder={
            configuration.hasApiKey
              ? configuration.apiKeyHint || t('chatAssistant.settings.apiKeyKeepExisting')
              : t('chatAssistant.settings.apiKeyPlaceholder')
          }
          inputAttr={{ 'data-testid': 'chatAssistantConfigurationApiKeyInput' }}
          onValueChanged={(event) => {
            setFormState((current) => ({
              ...current,
              apiKey: String(event.value ?? ''),
            }));
          }}
        />
      </label>

      {configuration.hasConfiguration && (
        <div className="chatAssistantSettingsSection__status">
          <CheckBox
            value={configuration.isEnabled}
            disabled={isLoading || isUpdatingStatus}
            text={t('chatAssistant.settings.enabledLabel')}
            elementAttr={{ 'data-testid': 'chatAssistantConfigurationStatusToggle' }}
            onValueChanged={(event) => {
              void toggleEnabled(Boolean(event.value));
            }}
          />
          {configuration.supportsVision && (
            <p className="chatAssistantSettingsSection__meta">
              {t('chatAssistant.settings.supportsVisionHint')}
            </p>
          )}
        </div>
      )}

      {saveErrorKey && (
        <p className="chatAssistantSettingsSection__error" data-testid="chatAssistantSettingsSaveError">
          {t(saveErrorKey)}
        </p>
      )}

      {statusErrorKey && (
        <p className="chatAssistantSettingsSection__error" data-testid="chatAssistantSettingsStatusError">
          {t(statusErrorKey)}
        </p>
      )}

      {saveSuccessVisible && (
        <p className="chatAssistantSettingsSection__success" data-testid="chatAssistantSettingsSaveSuccess">
          {t('chatAssistant.settings.saveSuccess')}
        </p>
      )}

      <div className="chatAssistantSettingsSection__actions">
        <Button
          type="default"
          stylingMode="contained"
          text={t('chatAssistant.settings.save')}
          disabled={isLoading || isSaving}
          elementAttr={{ 'data-testid': 'chatAssistantConfigurationSaveButton' }}
          onClick={() => {
            void saveConfiguration();
          }}
        />
      </div>
    </section>
  );
}
