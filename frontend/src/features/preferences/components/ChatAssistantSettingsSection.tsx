import Button from 'devextreme-react/button';
import { Column } from 'devextreme-react/data-grid';
import CheckBox from 'devextreme-react/check-box';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { confirmDelete } from '../../../shared/ui/abm';
import { DataGridDx } from '../../../shared/ui/grids';
import { useChatAssistantConfigurations } from '../../chatAssistant/hooks/useChatAssistantConfigurations';
import type { MyChatAssistantConfiguration } from '../../chatAssistant/model/myChatAssistantConfiguration';
import { findProviderCatalogItem } from '../../chatAssistant/model/providerCatalog';
import { ChatAssistantConfigurationFormPopup } from './ChatAssistantConfigurationFormPopup';
import './ChatAssistantSettingsSection.css';

type ChatAssistantConfigurationGridRow = MyChatAssistantConfiguration & Record<string, unknown>;

export function ChatAssistantSettingsSection() {
  const { t } = useTranslation();
  const {
    catalogItems,
    configurations,
    editingConfiguration,
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
  } = useChatAssistantConfigurations();

  const gridRows = useMemo<ChatAssistantConfigurationGridRow[]>(
    () => configurations.map((configuration) => ({ ...configuration })),
    [configurations],
  );

  const resolveProviderLabel = (providerId: string) =>
    findProviderCatalogItem(catalogItems, providerId)?.displayName ?? providerId;

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

      {deleteErrorKey && (
        <p className="chatAssistantSettingsSection__error" data-testid="chatAssistantSettingsDeleteError">
          {t(deleteErrorKey)}
        </p>
      )}

      {saveSuccessVisible && !isFormOpen && (
        <p className="chatAssistantSettingsSection__success" data-testid="chatAssistantSettingsSaveSuccess">
          {t('chatAssistant.settings.saveSuccess')}
        </p>
      )}

      <DataGridDx<ChatAssistantConfigurationGridRow>
        proceso="pw_chat_assistant_config"
        gridId="configurations"
        dataSource={gridRows}
        keyExpr="credentialId"
        isLoading={isLoading}
        emptyMessageKey="chatAssistant.settings.emptyList"
        exportEnabled={false}
        enableGrouping={false}
        toolbarStart={
          <Button
            type="default"
            stylingMode="contained"
            text={t('chatAssistant.settings.addConfiguration')}
            onClick={openCreateForm}
            elementAttr={{ 'data-testid': 'chatAssistantConfigurationAddButton' }}
          />
        }
        rowActions={[
          {
            actionKey: 'edit',
            icon: 'edit',
            hintKey: 'chatAssistant.settings.editConfiguration',
            onClick: (row) => {
              openEditForm(row);
            },
          },
          {
            actionKey: 'delete',
            icon: 'trash',
            hintKey: 'chatAssistant.settings.deleteConfiguration',
            onClick: (row) => {
              void (async () => {
                const confirmed = await confirmDelete({
                  recordLabel: row.displayName,
                  t,
                });

                if (confirmed) {
                  await deleteConfiguration(row.credentialId);
                }
              })();
            },
          },
        ]}
      >
        <Column dataField="displayName" caption={t('chatAssistant.settings.displayNameLabel')} />
        <Column
          dataField="providerId"
          caption={t('chatAssistant.settings.providerLabel')}
          calculateCellValue={(row) => resolveProviderLabel(String(row.providerId ?? ''))}
        />
        <Column dataField="modelId" caption={t('chatAssistant.settings.modelIdLabel')} />
        <Column
          dataField="isEnabled"
          caption={t('chatAssistant.settings.enabledLabel')}
          width={120}
          cellRender={({ data }) => (
            <CheckBox
              value={Boolean(data.isEnabled)}
              disabled={isDeleting}
              elementAttr={{ 'data-testid': `chatAssistantConfigurationEnabled-${data.credentialId}` }}
              onValueChanged={(event) => {
                void toggleEnabled(data as MyChatAssistantConfiguration, Boolean(event.value));
              }}
            />
          )}
        />
      </DataGridDx>

      <ChatAssistantConfigurationFormPopup
        visible={isFormOpen}
        isSaving={isSaving}
        saveErrorKey={saveErrorKey}
        catalogItems={catalogItems}
        editingConfiguration={editingConfiguration}
        formState={formState}
        selectedProvider={selectedProvider}
        onClose={closeForm}
        onSave={() => {
          void saveConfiguration();
        }}
        onFormStateChange={setFormState}
      />
    </section>
  );
}
