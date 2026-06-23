import Button from 'devextreme-react/button';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import './ChatAssistantEmptyState.css';

export function ChatAssistantEmptyState() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  return (
    <section className="chatAssistantEmptyState" data-testid="chatAssistantEmptyState">
      <h2>{t('chatAssistant.emptyState.title')}</h2>
      <p>{t('chatAssistant.emptyState.message')}</p>
      <Button
        text={t('chatAssistant.emptyState.configurationCta')}
        type="default"
        stylingMode="contained"
        onClick={() => {
          navigate('/preferences');
        }}
        elementAttr={{ 'data-testid': 'chatAssistantEmptyStateConfigurationCta' }}
      />
    </section>
  );
}
