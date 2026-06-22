import { useMemo } from 'react';
import { loadInitialMessage } from '../content/loadInitialMessage';
import { ChatAssistantMarkdownMessage } from './ChatAssistantMarkdownMessage';
import './ChatAssistantMessageBubble.css';

type ChatAssistantInitialMessageProps = {
  fallbackText: string;
};

export function ChatAssistantInitialMessage({ fallbackText }: ChatAssistantInitialMessageProps) {
  const content = useMemo(() => {
    try {
      const loaded = loadInitialMessage().trim();
      return loaded !== '' ? loaded : fallbackText;
    } catch (error) {
      console.error('[chatAssistant] Failed to load initial message', error);
      return fallbackText;
    }
  }, [fallbackText]);

  if (content.trim() === '') {
    return null;
  }

  return (
    <article
      className="chatAssistantMessageBubble chatAssistantMessageBubble--assistant"
      data-testid="chatAssistantInitialMessage"
    >
      <ChatAssistantMarkdownMessage content={content} />
    </article>
  );
}
