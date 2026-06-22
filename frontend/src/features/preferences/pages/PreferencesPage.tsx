import { useTranslation } from 'react-i18next';
import { ChatAssistantSettingsSection } from '../components/ChatAssistantSettingsSection';
import './PreferencesPage.css';

export function PreferencesPage() {
  const { t } = useTranslation();

  return (
    <main className="preferencesPage" data-testid="preferencesPage">
      <header className="preferencesPage__header">
        <h1>{t('preferences.pageTitle')}</h1>
        <p>{t('preferences.pageIntro')}</p>
      </header>

      <ChatAssistantSettingsSection />
    </main>
  );
}
