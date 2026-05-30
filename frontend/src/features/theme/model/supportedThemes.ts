export const defaultThemeKey = 'generic.light';

export const supportedThemeKeys = ['generic.light', 'generic.dark'] as const;

export type SupportedThemeKey = (typeof supportedThemeKeys)[number];

export function isSupportedThemeKey(value: string): value is SupportedThemeKey {
  return (supportedThemeKeys as readonly string[]).includes(value);
}

export function themeNameKey(themeKey: SupportedThemeKey): string {
  return `theme.name.${themeKey}`;
}
