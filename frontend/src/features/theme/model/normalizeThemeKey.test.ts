import { describe, expect, it } from 'vitest';
import { normalizeThemeKey } from './normalizeThemeKey';

describe('normalizeThemeKey', () => {
  it('normaliza alias legacy light', () => {
    expect(normalizeThemeKey('light')).toBe('generic.light');
  });

  it('conserva generic.dark', () => {
    expect(normalizeThemeKey('generic.dark')).toBe('generic.dark');
  });

  it('aplica fallback para tema invalido', () => {
    expect(normalizeThemeKey('xx')).toBe('generic.light');
  });
});
