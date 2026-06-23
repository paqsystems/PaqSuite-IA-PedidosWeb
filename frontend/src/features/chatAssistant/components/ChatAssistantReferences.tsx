import { useTranslation } from 'react-i18next';
import type { ChatAssistantDocumentReference } from '../model/chatAssistantMessage';
import './ChatAssistantReferences.css';

type ChatAssistantReferencesProps = {
  references: ChatAssistantDocumentReference[];
};

export function ChatAssistantReferences({ references }: ChatAssistantReferencesProps) {
  const { t } = useTranslation();

  if (references.length === 0) {
    return null;
  }

  return (
    <section className="chatAssistantReferences" data-testid="chatAssistantReferences">
      <h3 className="chatAssistantReferences__title">{t('chatAssistant.references.title')}</h3>
      <ul className="chatAssistantReferences__list">
        {references.map((reference) => (
          <li key={reference.path}>
            <span className="chatAssistantReferences__titleText">{reference.title}</span>
            <span className="chatAssistantReferences__path">{reference.path}</span>
          </li>
        ))}
      </ul>
    </section>
  );
}
