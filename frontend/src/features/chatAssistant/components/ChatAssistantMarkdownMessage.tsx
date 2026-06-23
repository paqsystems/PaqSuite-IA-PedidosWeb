import { useMemo } from 'react';
import { renderSafeMarkdown } from '../utils/renderSafeMarkdown';
import './ChatAssistantMarkdownMessage.css';

type ChatAssistantMarkdownMessageProps = {
  content: string;
  className?: string;
};

export function ChatAssistantMarkdownMessage({
  content,
  className,
}: ChatAssistantMarkdownMessageProps) {
  const html = useMemo(() => renderSafeMarkdown(content), [content]);

  if (html === '') {
    return null;
  }

  return (
    <div
      className={['chatAssistantMarkdownMessage', className].filter(Boolean).join(' ')}
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
}
