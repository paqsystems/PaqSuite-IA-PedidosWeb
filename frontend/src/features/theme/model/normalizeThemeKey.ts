import { defaultThemeKey, isSupportedThemeKey } from './supportedThemes';

const legacyThemeAliases: Record<string, string> = {
  default: 'generic.light',
  light: 'generic.light',
  dark: 'generic.dark',
};

export function normalizeThemeKey(theme: string | null | undefined): string {
  if (theme === undefined || theme === null || theme.trim() === '') {
    return defaultThemeKey;
  }

  const trimmedTheme = theme.trim();
  const resolvedTheme = legacyThemeAliases[trimmedTheme] ?? trimmedTheme;

  if (isSupportedThemeKey(resolvedTheme)) {
    return resolvedTheme;
  }

  return defaultThemeKey;
}

export function resolveInitialTheme(sessionTheme: string | null | undefined): string {
  return normalizeThemeKey(sessionTheme);
}
