import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import {
  chatAssistantSupportEmail,
  resolveChatAssistantProjectName,
} from '../config/chatAssistantContentConfig';
import { ChatAssistantComposer, type ChatAssistantComposerSubmitPayload } from '../components/ChatAssistantComposer';
import { ChatAssistantConversationPanel } from '../components/ChatAssistantConversationPanel';
import { ChatAssistantEmptyState } from '../components/ChatAssistantEmptyState';
import { useChatAssistantConversation } from '../hooks/useChatAssistantConversation';
import type { ChatAssistantMessage, ChatAssistantReply } from '../model/chatAssistantMessage';
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
  onSubmitMessage,
}: ChatAssistantPageViewProps) {
  const { t } = useTranslation();
  const projectName = useMemo(() => resolveChatAssistantProjectName(), []);

  const initialFallbackText = t('chatAssistant.messages.initialFallback', {
    projectName,
  });
  const supportFollowupFallbackText = t('chatAssistant.messages.supportFollowupFallback', {
    supportEmail: chatAssistantSupportEmail,
  });

  return (
    <main className="chatAssistantPage" data-testid="chatAssistantPage">
      <header className="chatAssistantPage__header">
        <h1>{t('chatAssistant.page.title')}</h1>
        <p>{t('chatAssistant.page.intro')}</p>
      </header>

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
    isLoading,
    isOperational,
    isSubmitting,
    lastReply,
    loadErrorKey,
    messages,
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
      onSubmitMessage={sendMessage}
      submitErrorKey={submitErrorKey}
      supportsVision={supportsVision}
    />
  );
}
