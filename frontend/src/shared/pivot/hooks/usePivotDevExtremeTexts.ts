import { useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { syncDevExtremeLocale } from '../../../features/i18n/syncDevExtremeLocale';

export function usePivotDevExtremeTexts() {
  const { i18n } = useTranslation();

  useEffect(() => {
    syncDevExtremeLocale(i18n.language);
  }, [i18n.language]);

  return useMemo(
    () => ({
      localeKey: i18n.language,
    }),
    [i18n.language],
  );
}
