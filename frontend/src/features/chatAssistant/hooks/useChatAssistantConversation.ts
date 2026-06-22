import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ApiClientError } from '../../../shared/http/client';
import { getMyChatAssistantConfiguration } from '../api/getMyChatAssistantConfiguration';
import { getProviderCatalog } from '../api/getProviderCatalog';
import { sendChatAssistantMessage } from '../api/sendChatAssistantMessage';
import type { ChatAssistantComposerSubmitPayload } from '../components/ChatAssistantComposer';
import {
  emptyMyChatAssistantConfiguration,
  type MyChatAssistantConfiguration,
} from '../model/myChatAssistantConfiguration';
import type { ChatAssistantMessage, ChatAssistantReply } from '../model/chatAssistantMessage';
import { isChatAssistantConfigurationOperational } from '../utils/resolveChatAssistantOperationalConfiguration';

function createMessageId(): string {
  return `msg-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

export function useChatAssistantConversation() {
  const { t } = useTranslation();
  const [configuration, setConfiguration] = useState<MyChatAssistantConfiguration>(
    emptyMyChatAssistantConfiguration,
  );
  const [activeProviderIds, setActiveProviderIds] = useState<string[]>([]);
  const [messages, setMessages] = useState<ChatAssistantMessage[]>([]);
  const [lastReply, setLastReply] = useState<ChatAssistantReply | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [loadErrorKey, setLoadErrorKey] = useState<string | null>(null);
  const [submitErrorKey, setSubmitErrorKey] = useState<string | null>(null);

  const loadState = useCallback(async () => {
    setIsLoading(true);
    setLoadErrorKey(null);

    try {
      const [catalog, currentConfiguration] = await Promise.all([
        getProviderCatalog(),
        getMyChatAssistantConfiguration(),
      ]);

      setActiveProviderIds(catalog.items.map((item) => item.providerId));
      setConfiguration(currentConfiguration);
    } catch {
      setLoadErrorKey('chatAssistant.page.loadFailed');
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadState();
  }, [loadState]);

  const isOperational = useMemo(
    () => isChatAssistantConfigurationOperational(configuration, activeProviderIds),
    [activeProviderIds, configuration],
  );

  const sendMessage = useCallback(
    async ({ message, images }: ChatAssistantComposerSubmitPayload): Promise<boolean> => {
      setIsSubmitting(true);
      setSubmitErrorKey(null);

      const userMessageContent =
        message.trim() !== ''
          ? message.trim()
          : t('chatAssistant.composer.imagesOnlyMessage', { count: images.length });

      const userMessage: ChatAssistantMessage = {
        id: createMessageId(),
        role: 'user',
        content: userMessageContent,
      };

      setMessages((current) => [...current, userMessage]);

      try {
        const reply = await sendChatAssistantMessage({
          message,
          images: images.length > 0 ? images : undefined,
        });

        setLastReply(reply);
        setMessages((current) => [
          ...current,
          {
            id: createMessageId(),
            role: 'assistant',
            content: reply.reply,
            references: reply.references,
            requiresSupportFollowup: reply.requiresSupportFollowup,
          },
        ]);

        return true;
      } catch (error) {
        if (error instanceof ApiClientError && error.respuestaKey.startsWith('chatAssistant.')) {
          setSubmitErrorKey(error.respuestaKey);
        } else {
          setSubmitErrorKey('chatAssistant.composer.submitFailed');
        }
        setMessages((current) => current.filter((item) => item.id !== userMessage.id));

        return false;
      } finally {
        setIsSubmitting(false);
      }
    },
    [t],
  );

  return {
    configuration,
    isLoading,
    isOperational,
    isSubmitting,
    lastReply,
    loadErrorKey,
    messages,
    sendMessage,
    submitErrorKey,
    supportsVision: configuration.supportsVision,
  };
}
