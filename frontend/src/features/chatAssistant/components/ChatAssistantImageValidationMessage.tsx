import { useTranslation } from 'react-i18next';
import './ChatAssistantImageValidationMessage.css';

type ChatAssistantImageValidationMessageProps = {
  errorKey: string | null;
};

export function ChatAssistantImageValidationMessage({
  errorKey,
}: ChatAssistantImageValidationMessageProps) {
  const { t } = useTranslation();

  if (!errorKey) {
    return null;
  }

  return (
    <p
      className="chatAssistantImageValidationMessage"
      role="alert"
      data-testid="chatAssistantImageValidationMessage"
    >
      {t(errorKey)}
    </p>
  );
}
