import { useCallback, useEffect, useState } from 'react';
import type { SessionContext } from '../auth/types';
import { patchOpenInNewTabPreference, preferencesRequest } from './preferencesApi';
import {
  defaultLocale,
  defaultOpenInNewTab,
  resolvePreferencesFromSession,
  type ResolvedUserPreferences,
} from './userPreferences';
import { normalizeThemeKey } from '../theme/model/normalizeThemeKey';

export function useUserPreferences(sessionContext: SessionContext) {
  const [preferences, setPreferences] = useState<ResolvedUserPreferences>(() =>
    resolvePreferencesFromSession(sessionContext),
  );
  const [isSavingOpenInNewTab, setIsSavingOpenInNewTab] = useState(false);

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
          theme: normalizeThemeKey(envelope.resultado.theme),
          openInNewTab: envelope.resultado.openInNewTab ?? defaultOpenInNewTab,
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

  const updateOpenInNewTab = useCallback(async (openInNewTab: boolean) => {
    const previousValue = preferences.openInNewTab;
    setPreferences((currentPreferences) => ({
      ...currentPreferences,
      openInNewTab,
    }));
    setIsSavingOpenInNewTab(true);

    try {
      const envelope = await patchOpenInNewTabPreference(openInNewTab);
      setPreferences((currentPreferences) => ({
        ...currentPreferences,
        openInNewTab: envelope.resultado.openInNewTab,
      }));
    } catch {
      setPreferences((currentPreferences) => ({
        ...currentPreferences,
        openInNewTab: previousValue,
      }));
    } finally {
      setIsSavingOpenInNewTab(false);
    }
  }, [preferences.openInNewTab]);

  return {
    preferences,
    isSavingOpenInNewTab,
    updateOpenInNewTab,
  };
}
