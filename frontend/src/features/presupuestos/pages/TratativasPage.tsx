import { useTranslation } from 'react-i18next';

export function TratativasPage() {
  const { t } = useTranslation();

  return (
    <section data-testid="page-presupuestos-tratativas">
      <h2>{t('pages.presupuestosTratativas')}</h2>
      <p>{t('pages.placeholderDescription')}</p>
    </section>
  );
}
