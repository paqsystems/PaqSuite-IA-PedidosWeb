import themes from 'devextreme/ui/themes';
import dxDarkStylesheetUrl from 'devextreme/dist/css/dx.dark.css?url';
import dxLightStylesheetUrl from 'devextreme/dist/css/dx.light.css?url';
import { defaultThemeKey, supportedThemeKeys } from './model/supportedThemes';
import { normalizeThemeKey } from './model/normalizeThemeKey';

const stylesheetUrlByTheme: Record<string, string> = {
  'generic.light': dxLightStylesheetUrl,
  'generic.dark': dxDarkStylesheetUrl,
};

const dxThemeLinkSelector = 'link[rel="dx-theme"]';

let themesBootstrapped = false;

function bootstrapDevExtremeThemes(): void {
  if (themesBootstrapped) {
    return;
  }

  if (document.querySelector(dxThemeLinkSelector) === null) {
    for (const themeKey of supportedThemeKeys) {
      const link = document.createElement('link');
      link.rel = 'dx-theme';
      link.setAttribute('data-theme', themeKey);
      link.href = stylesheetUrlByTheme[themeKey];
      if (themeKey === defaultThemeKey) {
        link.setAttribute('data-active', 'true');
      }
      document.head.appendChild(link);
    }
  }

  themes.resetTheme();
  themes.init({ theme: defaultThemeKey });
  themesBootstrapped = true;
}

export function syncDevExtremeTheme(themeKey: string): void {
  const normalizedTheme = normalizeThemeKey(themeKey);

  bootstrapDevExtremeThemes();
  themes.current(normalizedTheme);
  document.documentElement.setAttribute('data-theme', normalizedTheme);
}
