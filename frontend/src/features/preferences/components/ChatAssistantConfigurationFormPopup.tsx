import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import TextBox from 'devextreme-react/text-box';
import { useTranslation } from 'react-i18next';
import { SelectBoxDx } from '../../../shared/ui/controls/SelectBoxDx';
import type { ChatAssistantConfigurationFormState } from '../../chatAssistant/hooks/useChatAssistantConfigurations';
import type { MyChatAssistantConfiguration } from '../../chatAssistant/model/myChatAssistantConfiguration';
import type { ChatAssistantProviderCatalogItem } from '../../chatAssistant/model/providerCatalog';
import { ChatAssistantModelSelect } from './ChatAssistantModelSelect';
import { ChatAssistantProviderFields } from './ChatAssistantProviderFields';
import { ChatAssistantProviderHelpLink } from './ChatAssistantProviderHelpLink';
import './ChatAssistantConfigurationFormPopup.css';

type ChatAssistantConfigurationFormPopupProps = {
  visible: boolean;
  isSaving: boolean;
  saveErrorKey: string | null;
  catalogItems: ChatAssistantProviderCatalogItem[];
  editingConfiguration: MyChatAssistantConfiguration;
  formState: ChatAssistantConfigurationFormState;
  selectedProvider: ChatAssistantProviderCatalogItem | null;
  onClose: () => void;
  onSave: () => void;
  onFormStateChange: (
    updater: (current: ChatAssistantConfigurationFormState) => ChatAssistantConfigurationFormState,
  ) => void;
};

export function ChatAssistantConfigurationFormPopup({
  visible,
  isSaving,
  saveErrorKey,
  catalogItems,
  editingConfiguration,
  formState,
  selectedProvider,
  onClose,
  onSave,
  onFormStateChange,
}: ChatAssistantConfigurationFormPopupProps) {
  const { t } = useTranslation();
  const isEditing = editingConfiguration.credentialId > 0;

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      showCloseButton
      dragEnabled={false}
      hideOnOutsideClick={false}
      title={
        isEditing
          ? t('chatAssistant.settings.editConfigurationTitle')
          : t('chatAssistant.settings.addConfigurationTitle')
      }
      width={560}
      maxHeight="90vh"
      elementAttr={{ 'data-testid': 'chatAssistantConfigurationFormPopup' }}
    >
      <div className="chatAssistantConfigurationFormPopup">
        <label className="chatAssistantConfigurationFormPopup__field">
          <span>{t('chatAssistant.settings.displayNameLabel')}</span>
          <TextBox
            value={formState.displayName}
            inputAttr={{ 'data-testid': 'chatAssistantConfigurationDisplayNameInput' }}
            onValueChanged={(event) => {
              onFormStateChange((current) => ({
                ...current,
                displayName: String(event.value ?? ''),
              }));
            }}
          />
        </label>

        <label className="chatAssistantConfigurationFormPopup__field">
          <span>{t('chatAssistant.settings.providerLabel')}</span>
          <SelectBoxDx
            dataSource={catalogItems}
            displayExpr="displayName"
            valueExpr="providerId"
            value={formState.providerId}
            searchEnabled
            showClearButton
            inputAttr={{ 'data-testid': 'chatAssistantConfigurationProviderSelect' }}
            elementAttr={{ 'data-testid': 'chatAssistantProviderSelectBox' }}
            onValueChanged={(event) => {
              onFormStateChange((current) => ({
                ...current,
                providerId: (event.value as string | null) ?? null,
                modelId: '',
              }));
            }}
          />
        </label>

        <ChatAssistantProviderHelpLink provider={selectedProvider} />

        <ChatAssistantModelSelect
          providerId={formState.providerId}
          catalogItems={catalogItems}
          modelId={formState.modelId}
          onModelIdChange={(modelId) => {
            onFormStateChange((current) => ({
              ...current,
              modelId,
            }));
          }}
        />

        <ChatAssistantProviderFields
          provider={selectedProvider}
          baseUrl={formState.baseUrl}
          onBaseUrlChange={(baseUrl) => {
            onFormStateChange((current) => ({
              ...current,
              baseUrl,
            }));
          }}
        />

        <label className="chatAssistantConfigurationFormPopup__field">
          <span>{t('chatAssistant.settings.apiKeyLabel')}</span>
          <TextBox
            mode="password"
            value={formState.apiKey}
            placeholder={
              editingConfiguration.hasApiKey
                ? editingConfiguration.apiKeyHint || t('chatAssistant.settings.apiKeyKeepExisting')
                : t('chatAssistant.settings.apiKeyPlaceholder')
            }
            inputAttr={{ 'data-testid': 'chatAssistantConfigurationApiKeyInput' }}
            onValueChanged={(event) => {
              onFormStateChange((current) => ({
                ...current,
                apiKey: String(event.value ?? ''),
              }));
            }}
          />
        </label>

        {isEditing && (
          <p className="chatAssistantConfigurationFormPopup__meta">
            {editingConfiguration.isEnabled
              ? t('chatAssistant.settings.enabledHint')
              : t('chatAssistant.settings.disabledHint')}
          </p>
        )}

        {saveErrorKey && (
          <p className="chatAssistantConfigurationFormPopup__error" data-testid="chatAssistantSettingsSaveError">
            {t(saveErrorKey)}
          </p>
        )}

        <div className="chatAssistantConfigurationFormPopup__actions">
          <Button
            text={t('abm.cancel')}
            stylingMode="outlined"
            onClick={onClose}
            elementAttr={{ 'data-testid': 'chatAssistantConfigurationFormCancel' }}
          />
          <Button
            type="default"
            stylingMode="contained"
            text={t('chatAssistant.settings.save')}
            disabled={isSaving}
            elementAttr={{ 'data-testid': 'chatAssistantConfigurationSaveButton' }}
            onClick={() => {
              void onSave();
            }}
          />
        </div>
      </div>
    </Popup>
  );
}
