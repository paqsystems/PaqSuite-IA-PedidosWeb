import { describe, expect, it } from 'vitest';
import { isRouteAllowedOnMobile } from '../../features/mobile/mobileMenuPolicy';

describe('mobileMenuPolicy', () => {
  it('bloquea rutas excluidas en mobile', () => {
    expect(isRouteAllowedOnMobile('/admin/roles')).toBe(false);
    expect(isRouteAllowedOnMobile('/excel-import/historial')).toBe(false);
    expect(isRouteAllowedOnMobile('/consultas/stock')).toBe(true);
  });
});
