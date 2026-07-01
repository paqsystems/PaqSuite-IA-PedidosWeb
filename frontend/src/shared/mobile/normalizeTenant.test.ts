import { describe, expect, it } from 'vitest';
import { isValidTenantSlug, normalizeTenant } from './normalizeTenant';

describe('normalizeTenant', () => {
  it('normaliza a minusculas y recorta espacios', () => {
    expect(normalizeTenant('  Desarrollo ')).toBe('desarrollo');
  });

  it('valida slug tenant', () => {
    expect(isValidTenantSlug('ankasdelsur')).toBe(true);
    expect(isValidTenantSlug('')).toBe(false);
    expect(isValidTenantSlug('tenant invalido')).toBe(false);
  });
});
