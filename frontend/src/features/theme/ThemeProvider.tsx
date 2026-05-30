import { useCallback, useEffect, useMemo, useState } from 'react';
import { useAuth } from '../auth/AuthProvider';
import { updateStoredSessionContext } from '../auth/authStorage';
import { ApiClientError } from '../../shared/http/client';
import { updateThemePreference } from './api/updateThemePreference';
import { CurrentThemeContext } from './hooks/useCurrentTheme';
import { normalizeThemeKey, resolveInitialTheme } from './model/normalizeThemeKey';
import { defaultThemeKey } from './model/supportedThemes';
import { syncDevExtremeTheme } from './syncDevExtremeTheme';

type ThemeProviderProps = {
  children: React.ReactNode;
};

function applyTheme(themeKey: string): void {
  syncDevExtremeTheme(themeKey);
}

export function ThemeProvider({ children }: ThemeProviderProps) {
  const { sessionContext, isAuthenticated, setSessionContext } = useAuth();
  const [currentTheme, setCurrentTheme] = useState(() =>
    isAuthenticated
      ? resolveInitialTheme(sessionContext?.theme)
      : defaultThemeKey,
  );
  const [isSaving, setIsSaving] = useState(false);
  const [saveErrorKey, setSaveErrorKey] = useState<string | null>(null);

  useEffect(() => {
    const nextTheme = isAuthenticated
      ? resolveInitialTheme(sessionContext?.theme)
      : defaultThemeKey;

    applyTheme(nextTheme);
    setCurrentTheme((previousTheme) => (previousTheme === nextTheme ? previousTheme : nextTheme));
  }, [isAuthenticated, sessionContext?.theme]);

  const changeTheme = useCallback(
    async (requestedTheme: string) => {
      const normalizedTheme = normalizeThemeKey(requestedTheme);

      if (normalizedTheme === currentTheme) {
        return;
      }

      if (!isAuthenticated || sessionContext === null) {
        applyTheme(normalizedTheme);
        setCurrentTheme(normalizedTheme);
        return;
      }

      const previousTheme = currentTheme;
      setSaveErrorKey(null);
      setIsSaving(true);
      setCurrentTheme(normalizedTheme);
      applyTheme(normalizedTheme);

      try {
        const envelope = await updateThemePreference(normalizedTheme);
        const persistedTheme = normalizeThemeKey(envelope.resultado.theme);
        const nextSessionContext = {
          ...sessionContext,
          theme: persistedTheme,
        };

        setSessionContext(nextSessionContext);
        updateStoredSessionContext(nextSessionContext);
        applyTheme(persistedTheme);
        setCurrentTheme(persistedTheme);
      } catch (error) {
        applyTheme(previousTheme);
        setCurrentTheme(previousTheme);

        if (error instanceof ApiClientError && error.respuestaKey) {
          setSaveErrorKey(error.respuestaKey);
        } else {
          setSaveErrorKey('preferences.themeSaveFailed');
        }
      } finally {
        setIsSaving(false);
      }
    },
    [currentTheme, isAuthenticated, sessionContext, setSessionContext],
  );

  const value = useMemo(
    () => ({
      currentTheme,
      changeTheme,
      isSaving,
      saveErrorKey,
    }),
    [changeTheme, currentTheme, isSaving, saveErrorKey],
  );

  return <CurrentThemeContext.Provider value={value}>{children}</CurrentThemeContext.Provider>;
}
