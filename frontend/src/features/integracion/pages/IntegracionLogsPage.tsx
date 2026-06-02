import { useTranslation } from 'react-i18next';

export function IntegracionLogsPage() {
  const { t } = useTranslation();

  return (
    <section data-testid="page-integracion-logs">
      <h2>{t('pages.integracionLogs')}</h2>
      <p>{t('pages.placeholderDescription')}</p>
    </section>
  );
}
