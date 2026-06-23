import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ApiClientError } from '../../../shared/http/client';
import { getMyChatAssistantConfigurations } from '../api/getMyChatAssistantConfigurations';
import { getProviderCatalog } from '../api/getProviderCatalog';
import { sendChatAssistantMessage } from '../api/sendChatAssistantMessage';
import type { ChatAssistantComposerSubmitPayload } from '../components/ChatAssistantComposer';
import type { MyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';
import type { ChatAssistantMessage, ChatAssistantReply } from '../model/chatAssistantMessage';
import {
  readStoredCredentialId,
  resolveSelectedOperationalConfiguration,
  storeCredentialId,
} from '../utils/resolveChatAssistantOperationalConfiguration';

function createMessageId(): string {
  return `msg-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

export function useChatAssistantConversation() {
  const { t } = useTranslation();
  const [configurations, setConfigurations] = useState<MyChatAssistantConfiguration[]>([]);
  const [selectedCredentialId, setSelectedCredentialId] = useState<number | null>(null);
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
      const [catalog, configurationList] = await Promise.all([
        getProviderCatalog(),
        getMyChatAssistantConfigurations(),
      ]);

      setActiveProviderIds(catalog.items.map((item) => item.providerId));
      setConfigurations(configurationList.items);

      const preferredCredentialId = readStoredCredentialId();
      const selectedConfiguration = resolveSelectedOperationalConfiguration(
        configurationList.items,
        catalog.items.map((item) => item.providerId),
        preferredCredentialId,
      );

      setSelectedCredentialId(selectedConfiguration?.credentialId ?? null);
    } catch {
      setLoadErrorKey('chatAssistant.page.loadFailed');
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadState();
  }, [loadState]);

  const operationalConfigurations = useMemo(
    () =>
      configurations.filter(
        (configuration) =>
          configuration.isEnabled
          && configuration.hasApiKey
          && configuration.providerId.trim() !== ''
          && activeProviderIds.includes(configuration.providerId),
      ),
    [activeProviderIds, configurations],
  );

  const selectedConfiguration = useMemo(
    () =>
      resolveSelectedOperationalConfiguration(
        configurations,
        activeProviderIds,
        selectedCredentialId,
      ),
    [activeProviderIds, configurations, selectedCredentialId],
  );

  const isOperational = operationalConfigurations.length > 0;

  const selectCredential = useCallback((credentialId: number | null) => {
    setSelectedCredentialId(credentialId);

    if (credentialId !== null) {
      storeCredentialId(credentialId);
    }
  }, []);

  const sendMessage = useCallback(
    async ({ message, images }: ChatAssistantComposerSubmitPayload): Promise<boolean> => {
      if (!selectedConfiguration) {
        setSubmitErrorKey('chatAssistant.configurationRequired');
        return false;
      }

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
          credentialId: selectedConfiguration.credentialId,
          images: images.length > 0 ? images : undefined,
        });

        setLastReply(reply);
        setMessages((current) => [
          ...current,
          {
            id: createMessageId(),
            role: 'assistant',
            content: reply.reply,
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
    [selectedConfiguration, t],
  );

  return {
    configurations: operationalConfigurations,
    selectedConfiguration,
    selectedCredentialId,
    isLoading,
    isOperational,
    isSubmitting,
    lastReply,
    loadErrorKey,
    messages,
    selectCredential,
    sendMessage,
    submitErrorKey,
    supportsVision: selectedConfiguration?.supportsVision ?? false,
  };
}
