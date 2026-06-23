import Button from 'devextreme-react/button';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { ChatAssistantSettingsSection } from '../components/ChatAssistantSettingsSection';
import './PreferencesPage.css';

export function PreferencesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  return (
    <main className="preferencesPage" data-testid="preferencesPage">
      <header className="preferencesPage__header">
        <div className="preferencesPage__headerMain">
          <h1>{t('preferences.pageTitle')}</h1>
          <p>{t('preferences.pageIntro')}</p>
        </div>
        <Button
          text={t('chatAssistant.settings.backToChat')}
          stylingMode="outlined"
          type="default"
          onClick={() => {
            navigate('/chat-assistant');
          }}
          elementAttr={{ 'data-testid': 'preferencesBackToChatButton' }}
        />
      </header>

      <ChatAssistantSettingsSection />
    </main>
  );
}
