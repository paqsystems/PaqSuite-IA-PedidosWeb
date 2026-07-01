import { describe, expect, it } from 'vitest';
import { normalizeApiBaseUrl } from './mobileRuntime';

describe('normalizeApiBaseUrl', () => {
  it('convierte backslashes de Windows a barras normales', () => {
    expect(normalizeApiBaseUrl('http:\\10.0.2.2:8088\\api\\v1')).toBe('http://10.0.2.2:8088/api/v1');
  });

  it('recorta slash final', () => {
    expect(normalizeApiBaseUrl('http://10.0.2.2:8088/api/v1/')).toBe('http://10.0.2.2:8088/api/v1');
  });
});
