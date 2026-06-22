import type { ChatAssistantReply } from '../model/chatAssistantMessage';
import { ChatAssistantReferences } from './ChatAssistantReferences';
import './ChatAssistantResponse.css';

type ChatAssistantResponseProps = {
  reply: ChatAssistantReply;
};

export function ChatAssistantResponse({ reply }: ChatAssistantResponseProps) {
  return (
    <article className="chatAssistantResponse" data-testid="chatAssistantResponse">
      <p className="chatAssistantResponse__text">{reply.reply}</p>
      <ChatAssistantReferences references={reply.references} />
    </article>
  );
}
