import { defaultLocale } from './supportedLocales';
import { normalizeLocaleWithFallback } from './normalizeLocale';
import { readGuestLocale } from './localeStorage';

type ResolveInitialLocaleOptions = {
  sessionLocale?: string | null;
  guestLocale?: string | null;
  navigatorLanguage?: string | null;
};

export function resolveInitialLocale(options: ResolveInitialLocaleOptions = {}): string {
  const guestLocale = options.guestLocale ?? readGuestLocale();
  const navigatorLanguage =
    options.navigatorLanguage ??
    (typeof navigator !== 'undefined' ? navigator.language : null);

  if (options.sessionLocale !== undefined && options.sessionLocale !== null) {
    return normalizeLocaleWithFallback(options.sessionLocale, guestLocale ?? navigatorLanguage);
  }

  return normalizeLocaleWithFallback(guestLocale, navigatorLanguage ?? defaultLocale);
}
