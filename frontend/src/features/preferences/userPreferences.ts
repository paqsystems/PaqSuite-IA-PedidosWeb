import type { SessionContext } from '../auth/types';

export const defaultLocale = 'es';
export const defaultTheme = 'generic.light';

export type ResolvedUserPreferences = {
  locale: string;
  theme: string;
};

export function resolvePreferencesFromSession(sessionContext: SessionContext): ResolvedUserPreferences {
  const locale = sessionContext.locale?.split('-')[0]?.trim();

  return {
    locale: locale !== undefined && locale !== '' ? locale : defaultLocale,
    theme: normalizeTheme(sessionContext.theme),
  };
}

export function normalizeTheme(theme: string | null | undefined): string {
  if (theme === undefined || theme === null || theme.trim() === '') {
    return defaultTheme;
  }

  if (theme === 'light') {
    return defaultTheme;
  }

  return theme;
}
