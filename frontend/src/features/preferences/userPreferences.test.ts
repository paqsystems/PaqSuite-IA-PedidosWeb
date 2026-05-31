import { describe, expect, it } from 'vitest';
import type { SessionContext } from '../auth/types';
import {
  defaultLocale,
  defaultTheme,
  normalizeTheme,
  resolvePreferencesFromSession,
} from './userPreferences';

const baseSession: SessionContext = {
  user: { id: 1, displayName: 'Test', login: 'test.mvp' },
  functionalProfile: 'cliente',
  codCliente: null,
  codVendedor: null,
  locale: 'es-AR',
  theme: 'light',
  firstLogin: false,
  inactivityTimeoutMinutes: 10,
  security: { roles: ['Cliente'], accesoTotal: false },
};

describe('userPreferences', () => {
  it('normaliza locale y theme desde la sesion', () => {
    expect(resolvePreferencesFromSession(baseSession)).toEqual({
      locale: 'es',
      theme: defaultTheme,
      openInNewTab: false,
    });
  });

  it('aplica fallback cuando faltan preferencias', () => {
    expect(
      resolvePreferencesFromSession({
        ...baseSession,
        locale: '',
        theme: 'xx',
      }),
    ).toEqual({
      locale: defaultLocale,
      theme: defaultTheme,
      openInNewTab: false,
    });
  });

  it('normaliza alias legacy light', () => {
    expect(normalizeTheme('light')).toBe(defaultTheme);
  });

  it('conserva themes distintos de light', () => {
    expect(normalizeTheme('generic.dark')).toBe('generic.dark');
  });
});
