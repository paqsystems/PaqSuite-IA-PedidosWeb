import { useCallback, useEffect, useMemo, useState } from 'react';
import i18n from './i18n';
import { updateLocalePreference } from './api/updateLocalePreference';
import { useAuth } from '../auth/AuthProvider';
import { updateStoredSessionContext } from '../auth/authStorage';
import { ApiClientError } from '../../shared/http/client';
import { CurrentLocaleContext } from './hooks/useCurrentLocale';
import { normalizeLocaleWithFallback } from './model/normalizeLocale';
import { resolveInitialLocale } from './model/resolveInitialLocale';
import { readGuestLocale, writeGuestLocale } from './model/localeStorage';
import { isSupportedLocale } from './model/supportedLocales';
import { syncDevExtremeLocale } from './syncDevExtremeLocale';

type LocaleProviderProps = {
  children: React.ReactNode;
};

async function applyLocale(localeCode: string): Promise<void> {
  await i18n.changeLanguage(localeCode);
  syncDevExtremeLocale(localeCode);
}

export function LocaleProvider({ children }: LocaleProviderProps) {
  const { sessionContext, isAuthenticated, setSessionContext } = useAuth();
  const [currentLocale, setCurrentLocale] = useState(() =>
    resolveInitialLocale({
      sessionLocale: sessionContext?.locale,
    }),
  );
  const [isSaving, setIsSaving] = useState(false);
  const [saveErrorKey, setSaveErrorKey] = useState<string | null>(null);

  useEffect(() => {
    const nextLocale = resolveInitialLocale({
      sessionLocale: isAuthenticated ? sessionContext?.locale : null,
      guestLocale: readGuestLocale(),
    });

    void applyLocale(nextLocale).then(() => {
      setCurrentLocale((previousLocale) =>
        previousLocale === nextLocale ? previousLocale : nextLocale,
      );
    });
  }, [isAuthenticated, sessionContext?.locale]);

  const changeLocale = useCallback(
    async (requestedLocale: string) => {
      if (!isSupportedLocale(requestedLocale) || requestedLocale === currentLocale) {
        return;
      }

      setSaveErrorKey(null);
      setIsSaving(true);

      try {
        if (isAuthenticated && sessionContext !== null) {
          const envelope = await updateLocalePreference(requestedLocale);
          const persistedLocale = normalizeLocaleWithFallback(envelope.resultado.locale, requestedLocale);
          const nextSessionContext = {
            ...sessionContext,
            locale: persistedLocale,
          };

          setSessionContext(nextSessionContext);
          updateStoredSessionContext(nextSessionContext);
          await applyLocale(persistedLocale);
          setCurrentLocale(persistedLocale);
          return;
        }

        writeGuestLocale(requestedLocale);
        await applyLocale(requestedLocale);
        setCurrentLocale(requestedLocale);
      } catch (error) {
        if (error instanceof ApiClientError && error.respuestaKey) {
          setSaveErrorKey(error.respuestaKey);
        } else {
          setSaveErrorKey('preferences.localeSaveFailed');
        }
      } finally {
        setIsSaving(false);
      }
    },
    [currentLocale, isAuthenticated, sessionContext, setSessionContext],
  );

  const value = useMemo(
    () => ({
      currentLocale,
      changeLocale,
      isSaving,
      saveErrorKey,
    }),
    [changeLocale, currentLocale, isSaving, saveErrorKey],
  );

  return <CurrentLocaleContext.Provider value={value}>{children}</CurrentLocaleContext.Provider>;
}
