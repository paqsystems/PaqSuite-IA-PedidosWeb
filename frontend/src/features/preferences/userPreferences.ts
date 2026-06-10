import type { SessionContext } from '../auth/types';
import { normalizeThemeKey } from '../theme/model/normalizeThemeKey';
import { defaultThemeKey } from '../theme/model/supportedThemes';

export const defaultLocale = 'es';
export const defaultTheme = defaultThemeKey;
export const defaultOpenInNewTab = false;

export type ResolvedUserPreferences = {
  locale: string;
  theme: string;
  openInNewTab: boolean;
};

export function resolvePreferencesFromSession(sessionContext: SessionContext): ResolvedUserPreferences {
  const locale = sessionContext.locale?.split('-')[0]?.trim();

  return {
    locale: locale !== undefined && locale !== '' ? locale : defaultLocale,
    theme: normalizeThemeKey(sessionContext.theme),
    openInNewTab: defaultOpenInNewTab,
  };
}

export function normalizeTheme(theme: string | null | undefined): string {
  return normalizeThemeKey(theme);
}
