import themes from 'devextreme/ui/themes';
import { defaultThemeKey } from './model/supportedThemes';
import { normalizeThemeKey } from './model/normalizeThemeKey';
import { resolveThemePalette } from './model/resolveThemePalette';
import { themeCssUrls } from './themeCssUrls';

const dxThemes = themes as typeof themes & {
  attachCssClasses?: (element: HTMLElement, themeName: string) => void;
  detachCssClasses?: (element: HTMLElement) => void;
};
const dxThemeLinkId = 'dx-theme-css';

const darkThemePrefixes = ['dark', 'darkmoon', 'darkviolet'] as const;
const darkThemeExact = new Set(['contrast', 'contrast.compact']);
const dxThemeAlias: Record<string, string> = {
  default: 'generic.light',
  light: 'generic.light',
  dark: 'generic.dark',
  'light.compact': 'generic.light.compact',
  'dark.compact': 'generic.dark.compact',
  carmine: 'generic.carmine',
  'carmine.compact': 'generic.carmine.compact',
  darkmoon: 'generic.darkmoon',
  'darkmoon.compact': 'generic.darkmoon.compact',
  darkviolet: 'generic.darkviolet',
  'darkviolet.compact': 'generic.darkviolet.compact',
  greenmist: 'generic.greenmist',
  'greenmist.compact': 'generic.greenmist.compact',
  softblue: 'generic.softblue',
  'softblue.compact': 'generic.softblue.compact',
  contrast: 'generic.contrast',
  'contrast.compact': 'generic.contrast.compact',
};

let lastDxThemeApplied: string | null = null;

function resolveDxThemeName(themeKey: string): string {
  return dxThemeAlias[themeKey] ?? themeKey;
}

function isDarkTheme(themeKey: string): boolean {
  return (
    themeKey.includes('.dark')
    || themeKey.includes('dark.')
    || darkThemeExact.has(themeKey)
    || darkThemePrefixes.some((prefix) => themeKey === prefix || themeKey.startsWith(`${prefix}.`))
  );
}

function syncDxViewportClasses(dxThemeName: string): void {
  const viewport = document.body;

  try {
    if (lastDxThemeApplied !== null) {
      dxThemes.detachCssClasses?.(viewport);
    }

    dxThemes.attachCssClasses?.(viewport, dxThemeName);
    lastDxThemeApplied = dxThemeName;
  } catch (error) {
    console.warn('[syncDevExtremeTheme] No se pudieron actualizar las clases DX', error);
  }
}

function ensureThemeStylesheet(themeKey: string): void {
  const url = themeCssUrls[themeKey] ?? themeCssUrls[defaultThemeKey];
  let link = document.getElementById(dxThemeLinkId) as HTMLLinkElement | null;

  if (link === null) {
    link = document.createElement('link');
    link.id = dxThemeLinkId;
    link.rel = 'stylesheet';
    link.type = 'text/css';
    document.head.appendChild(link);
  }

  if (link.href !== url) {
    link.href = url;
  }
}

function syncAppThemeVariables(themeKey: string): void {
  const palette = resolveThemePalette(themeKey);
  const root = document.documentElement;

  root.style.setProperty('--app-shell-accent-color', palette.accentColor);
  root.style.setProperty('--app-shell-accent-contrast-color', palette.accentContrastColor);
  root.style.setProperty('--app-shell-accent-soft-color', palette.accentSoftColor);
  root.style.setProperty('--app-shell-accent-soft-hover-color', palette.accentSoftHoverColor);
  root.style.setProperty('--app-shell-border-color', palette.borderColor);
  root.style.setProperty('--app-shell-surface-color', palette.surfaceColor);
  root.style.setProperty('--app-shell-panel-color', palette.panelColor);
  root.style.setProperty('--app-shell-panel-elevated-color', palette.panelElevatedColor);
  root.style.setProperty('--app-shell-text-color', palette.textColor);
  root.style.setProperty('--app-shell-muted-text-color', palette.mutedTextColor);
  root.style.setProperty('--app-shell-footer-background-color', palette.footerBackgroundColor);
  root.style.setProperty('--app-shell-footer-text-color', palette.footerTextColor);
  root.style.setProperty('--app-shell-footer-strong-text-color', palette.footerStrongTextColor);
  root.style.setProperty('--app-shell-overlay-color', palette.overlayColor);
  root.style.setProperty('--app-shell-danger-color', palette.dangerColor);
}

export function syncDevExtremeTheme(themeKey: string): void {
  const normalizedTheme = normalizeThemeKey(themeKey);
  const dxThemeName = resolveDxThemeName(normalizedTheme);

  ensureThemeStylesheet(normalizedTheme);
  syncDxViewportClasses(dxThemeName);
  syncAppThemeVariables(normalizedTheme);
  document.documentElement.setAttribute('data-theme', normalizedTheme);
  document.documentElement.setAttribute('data-color-scheme', isDarkTheme(normalizedTheme) ? 'dark' : 'light');
}
