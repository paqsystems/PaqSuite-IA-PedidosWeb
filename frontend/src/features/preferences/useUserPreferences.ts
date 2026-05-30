import { useEffect, useState } from 'react';
import type { SessionContext } from '../auth/types';
import { preferencesRequest } from './preferencesApi';
import {
  defaultLocale,
  defaultTheme,
  resolvePreferencesFromSession,
  type ResolvedUserPreferences,
} from './userPreferences';

export function useUserPreferences(sessionContext: SessionContext): ResolvedUserPreferences {
  const [preferences, setPreferences] = useState<ResolvedUserPreferences>(() =>
    resolvePreferencesFromSession(sessionContext),
  );

  useEffect(() => {
    let isCancelled = false;

    async function loadPreferences() {
      try {
        const envelope = await preferencesRequest();

        if (isCancelled) {
          return;
        }

        setPreferences({
          locale: envelope.resultado.locale?.trim() || defaultLocale,
          theme: envelope.resultado.theme?.trim() || defaultTheme,
        });
      } catch {
        if (!isCancelled) {
          setPreferences(resolvePreferencesFromSession(sessionContext));
        }
      }
    }

    loadPreferences();

    return () => {
      isCancelled = true;
    };
  }, [sessionContext]);

  return preferences;
}
