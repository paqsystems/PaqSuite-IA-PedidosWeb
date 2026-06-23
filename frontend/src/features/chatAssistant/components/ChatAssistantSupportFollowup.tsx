import { useMemo } from 'react';
import { loadSupportFollowupMessage } from '../content/loadSupportFollowupMessage';
import { ChatAssistantMarkdownMessage } from './ChatAssistantMarkdownMessage';
import './ChatAssistantMessageBubble.css';

type ChatAssistantSupportFollowupProps = {
  fallbackText: string;
};

export function ChatAssistantSupportFollowup({ fallbackText }: ChatAssistantSupportFollowupProps) {
  const content = useMemo(() => {
    try {
      const loaded = loadSupportFollowupMessage().trim();
      return loaded !== '' ? loaded : fallbackText;
    } catch (error) {
      console.error('[chatAssistant] Failed to load support followup message', error);
      return fallbackText;
    }
  }, [fallbackText]);

  if (content.trim() === '') {
    return null;
  }

  return (
    <aside
      className="chatAssistantMessageBubble chatAssistantMessageBubble--support"
      data-testid="chatAssistantSupportFollowup"
    >
      <ChatAssistantMarkdownMessage content={content} />
    </aside>
  );
}
