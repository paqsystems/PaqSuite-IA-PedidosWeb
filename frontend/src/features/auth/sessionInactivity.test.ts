import { describe, expect, it, vi } from 'vitest';
import {
  createInactivityController,
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

  it('createInactivityController mide desde ultima actividad', () => {
    vi.useFakeTimers();
    const onExpire = vi.fn();
    const controller = createInactivityController(120000, onExpire);

    vi.advanceTimersByTime(60000);
    controller.touch();
    vi.advanceTimersByTime(60000);

    expect(onExpire).not.toHaveBeenCalled();

    vi.advanceTimersByTime(120000);
    expect(onExpire).toHaveBeenCalledTimes(1);

    controller.dispose();
    vi.useRealTimers();
  });
});
