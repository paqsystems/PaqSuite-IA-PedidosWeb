import { useTranslation } from 'react-i18next';

export function AppearanceStubPage() {
  const { t } = useTranslation();

  return (
    <section data-testid="appearanceStubPage">
      <h1>{t('avatar.appearance.title')}</h1>
      <p>{t('avatar.appearance.stubMessage')}</p>
    </section>
  );
}
