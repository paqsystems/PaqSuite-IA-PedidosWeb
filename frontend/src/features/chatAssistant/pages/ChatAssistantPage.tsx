import Button from 'devextreme-react/button';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { SelectBoxDx } from '../../../shared/ui/controls/SelectBoxDx';
import {
  chatAssistantSupportEmail,
  resolveChatAssistantProjectName,
} from '../config/chatAssistantContentConfig';
import { ChatAssistantComposer, type ChatAssistantComposerSubmitPayload } from '../components/ChatAssistantComposer';
import { ChatAssistantConversationPanel } from '../components/ChatAssistantConversationPanel';
import { ChatAssistantEmptyState } from '../components/ChatAssistantEmptyState';
import { useChatAssistantConversation } from '../hooks/useChatAssistantConversation';
import type { ChatAssistantMessage, ChatAssistantReply } from '../model/chatAssistantMessage';
import type { MyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';
import './ChatAssistantPage.css';

export type ChatAssistantPageViewProps = {
  messages?: ChatAssistantMessage[];
  lastReply?: ChatAssistantReply | null;
  isOperational?: boolean;
  isLoading?: boolean;
  isSubmitting?: boolean;
  loadErrorKey?: string | null;
  submitErrorKey?: string | null;
  supportsVision?: boolean;
  operationalConfigurations?: MyChatAssistantConfiguration[];
  selectedCredentialId?: number | null;
  onSelectCredential?: (credentialId: number | null) => void;
  onSubmitMessage?: (payload: ChatAssistantComposerSubmitPayload) => Promise<boolean>;
};

export function ChatAssistantPageView({
  messages = [],
  lastReply = null,
  isOperational = true,
  isLoading = false,
  isSubmitting = false,
  loadErrorKey = null,
  submitErrorKey = null,
  supportsVision = false,
  operationalConfigurations = [],
  selectedCredentialId = null,
  onSelectCredential,
  onSubmitMessage,
}: ChatAssistantPageViewProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const projectName = useMemo(() => resolveChatAssistantProjectName(), []);
  const showConfigurationSelector = operationalConfigurations.length > 1;

  const initialFallbackText = t('chatAssistant.messages.initialFallback', {
    projectName,
  });
  const supportFollowupFallbackText = t('chatAssistant.messages.supportFollowupFallback', {
    supportEmail: chatAssistantSupportEmail,
  });

  return (
    <main className="chatAssistantPage" data-testid="chatAssistantPage">
      <header className="chatAssistantPage__header">
        <div className="chatAssistantPage__headerMain">
          <h1>{t('chatAssistant.page.title')}</h1>
          <p>{t('chatAssistant.page.intro')}</p>
        </div>
        <div className="chatAssistantPage__headerActions">
          {isOperational && (
            <Button
              text={t('chatAssistant.page.preferencesCta')}
              stylingMode="outlined"
              type="default"
              onClick={() => {
                navigate('/preferences');
              }}
              elementAttr={{ 'data-testid': 'chatAssistantPreferencesButton' }}
            />
          )}
        </div>
      </header>

      {showConfigurationSelector && (
        <label className="chatAssistantPage__configurationSelect">
          <span>{t('chatAssistant.page.configurationLabel')}</span>
          <SelectBoxDx
            dataSource={operationalConfigurations}
            displayExpr="displayName"
            valueExpr="credentialId"
            value={selectedCredentialId}
            searchEnabled
            inputAttr={{ 'data-testid': 'chatAssistantConfigurationSelect' }}
            elementAttr={{ 'data-testid': 'chatAssistantConfigurationSelectBox' }}
            onValueChanged={(event) => {
              onSelectCredential?.((event.value as number | null) ?? null);
            }}
          />
        </label>
      )}

      {loadErrorKey ? (
        <p className="chatAssistantPage__error" role="alert">
          {t(loadErrorKey)}
        </p>
      ) : null}

      {isLoading ? (
        <p className="chatAssistantPage__loading">{t('chatAssistant.page.loading')}</p>
      ) : !isOperational ? (
        <ChatAssistantEmptyState />
      ) : (
        <>
          <ChatAssistantConversationPanel
            messages={messages}
            lastReply={lastReply}
            initialFallbackText={initialFallbackText}
            supportFollowupFallbackText={supportFollowupFallbackText}
          />

          {submitErrorKey ? (
            <p className="chatAssistantPage__error" role="alert">
              {t(submitErrorKey)}
            </p>
          ) : null}

          <ChatAssistantComposer
            disabled={!isOperational}
            isSubmitting={isSubmitting}
            supportsVision={supportsVision}
            onSubmit={async (payload) => {
              if (!onSubmitMessage) {
                return false;
              }

              return onSubmitMessage(payload);
            }}
          />
        </>
      )}
    </main>
  );
}

export function ChatAssistantPage() {
  const {
    configurations,
    isLoading,
    isOperational,
    isSubmitting,
    lastReply,
    loadErrorKey,
    messages,
    selectCredential,
    selectedCredentialId,
    sendMessage,
    submitErrorKey,
    supportsVision,
  } = useChatAssistantConversation();

  return (
    <ChatAssistantPageView
      isLoading={isLoading}
      isOperational={isOperational}
      isSubmitting={isSubmitting}
      lastReply={lastReply}
      loadErrorKey={loadErrorKey}
      messages={messages}
      operationalConfigurations={configurations}
      selectedCredentialId={selectedCredentialId}
      onSelectCredential={selectCredential}
      onSubmitMessage={sendMessage}
      submitErrorKey={submitErrorKey}
      supportsVision={supportsVision}
    />
  );
}
