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
  security: { roles: ['Cliente'], accesoTotal: false },
};

describe('userPreferences', () => {
  it('normaliza locale y theme desde la sesion', () => {
    expect(resolvePreferencesFromSession(baseSession)).toEqual({
      locale: 'es',
      theme: defaultTheme,
    });
  });

  it('aplica fallback cuando faltan preferencias', () => {
    expect(
      resolvePreferencesFromSession({
        ...baseSession,
        locale: '',
        theme: '',
      }),
    ).toEqual({
      locale: defaultLocale,
      theme: defaultTheme,
    });
  });

  it('conserva themes distintos de light', () => {
    expect(normalizeTheme('generic.dark')).toBe('generic.dark');
  });
});
