import { describe, expect, it } from 'vitest';
import {
  resolveInactivityTimeoutMinutes,
  resolveInactivityTimeoutMs,
  shouldTrackInactivityKey,
} from './sessionInactivity';

describe('sessionInactivity', () => {
  it('usa 10 minutos cuando el valor no existe o es invalido', () => {
    expect(resolveInactivityTimeoutMinutes(undefined)).toBe(10);
    expect(resolveInactivityTimeoutMinutes(null)).toBe(10);
    expect(resolveInactivityTimeoutMinutes(0)).toBe(10);
    expect(resolveInactivityTimeoutMinutes(-5)).toBe(10);
  });

  it('convierte minutos validos a milisegundos', () => {
    expect(resolveInactivityTimeoutMs(10)).toBe(600000);
    expect(resolveInactivityTimeoutMs(2)).toBe(120000);
  });

  it('no considera Tab aislado como actividad valida', () => {
    expect(shouldTrackInactivityKey('Tab')).toBe(false);
    expect(shouldTrackInactivityKey('Enter')).toBe(true);
    expect(shouldTrackInactivityKey('a')).toBe(true);
  });
});
