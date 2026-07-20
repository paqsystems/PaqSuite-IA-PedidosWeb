import { describe, expect, it } from 'vitest';
import { isRouteAllowedOnMobile } from './mobileMenuPolicy';

describe('mobileMenuPolicy importacion masiva', () => {
  it('excluye la ruta de importacion masiva en native', () => {
    expect(isRouteAllowedOnMobile('/pedidos/importacion-masiva')).toBe(false);
  });
});
