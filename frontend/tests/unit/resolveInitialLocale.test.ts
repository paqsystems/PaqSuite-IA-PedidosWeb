import { describe, expect, it } from 'vitest';
import { normalizeLocale, normalizeLocaleWithFallback } from '../../src/features/i18n/model/normalizeLocale';
import { resolveInitialLocale } from '../../src/features/i18n/model/resolveInitialLocale';

describe('normalizeLocale', () => {
  it('normaliza BCP47 a codigo de catalogo', () => {
    expect(normalizeLocale('es-AR')).toBe('es');
    expect(normalizeLocale('en-US')).toBe('en');
    expect(normalizeLocale('it-IT')).toBe('it');
  });

  it('rechaza codigos fuera del catalogo', () => {
    expect(normalizeLocale('de-DE')).toBeNull();
    expect(normalizeLocale('')).toBeNull();
  });

  it('aplica fallback en cadena', () => {
    expect(normalizeLocaleWithFallback('xx', 'en-US')).toBe('en');
    expect(normalizeLocaleWithFallback(null, null)).toBe('es');
  });
});

describe('resolveInitialLocale', () => {
  it('prioriza locale de sesion sobre guest y navigator', () => {
    expect(
      resolveInitialLocale({
        sessionLocale: 'it-IT',
        guestLocale: 'en',
        navigatorLanguage: 'fr-FR',
      }),
    ).toBe('it');
  });

  it('usa guest y luego navigator para invitado', () => {
    expect(
      resolveInitialLocale({
        guestLocale: 'en',
        navigatorLanguage: 'fr-FR',
      }),
    ).toBe('en');

    expect(
      resolveInitialLocale({
        guestLocale: null,
        navigatorLanguage: 'fr-FR',
      }),
    ).toBe('fr');
  });
});
