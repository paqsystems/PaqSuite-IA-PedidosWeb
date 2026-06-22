import type { ChatAssistantMessage, ChatAssistantReply } from '../model/chatAssistantMessage';
import { ChatAssistantInitialMessage } from './ChatAssistantInitialMessage';
import { ChatAssistantResponse } from './ChatAssistantResponse';
import { ChatAssistantSupportFollowup } from './ChatAssistantSupportFollowup';
import './ChatAssistantConversationPanel.css';
import './ChatAssistantMessageBubble.css';

type ChatAssistantConversationPanelProps = {
  messages: ChatAssistantMessage[];
  lastReply: ChatAssistantReply | null;
  initialFallbackText: string;
  supportFollowupFallbackText: string;
};

export function ChatAssistantConversationPanel({
  messages,
  lastReply,
  initialFallbackText,
  supportFollowupFallbackText,
}: ChatAssistantConversationPanelProps) {
  const showInitialMessage = messages.length === 0;
  const showSupportFollowup =
    lastReply?.requiresSupportFollowup === true ||
    messages.some((message) => message.requiresSupportFollowup === true);

  return (
    <section className="chatAssistantConversationPanel" data-testid="chatAssistantConversationPanel">
      {showInitialMessage && <ChatAssistantInitialMessage fallbackText={initialFallbackText} />}

      {messages.map((message) => {
        if (message.role === 'user') {
          return (
            <article
              key={message.id}
              className="chatAssistantMessageBubble chatAssistantMessageBubble--user"
              data-testid="chatAssistantMessage-user"
            >
              <p className="chatAssistantMessageBubble__plainText">{message.content}</p>
            </article>
          );
        }

        return (
          <div key={message.id} data-testid="chatAssistantMessage-assistant">
            <ChatAssistantResponse
              reply={{
                reply: message.content,
                references: message.references ?? [],
                requiresSupportFollowup: message.requiresSupportFollowup ?? false,
              }}
            />
          </div>
        );
      })}

      {showSupportFollowup ? (
        <ChatAssistantSupportFollowup fallbackText={supportFollowupFallbackText} />
      ) : (
        <span data-testid="chatAssistantSupportFollowupHidden" hidden aria-hidden="true" />
      )}
    </section>
  );
}
