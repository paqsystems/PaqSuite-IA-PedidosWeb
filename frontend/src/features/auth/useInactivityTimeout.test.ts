import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createInactivityController } from './sessionInactivity';

describe('createInactivityController', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('expira tras el umbral sin actividad intermedia', () => {
    const onExpire = vi.fn();
    const controller = createInactivityController(2 * 60 * 1000, onExpire);

    vi.advanceTimersByTime(2 * 60 * 1000);

    expect(onExpire).toHaveBeenCalledTimes(1);
    controller.dispose();
  });

  it('reinicia el contador ante actividad del usuario', () => {
    const onExpire = vi.fn();
    const controller = createInactivityController(2 * 60 * 1000, onExpire);

    vi.advanceTimersByTime(90 * 1000);
    controller.touch();
    vi.advanceTimersByTime(90 * 1000);

    expect(onExpire).not.toHaveBeenCalled();

    vi.advanceTimersByTime(2 * 60 * 1000);

    expect(onExpire).toHaveBeenCalledTimes(1);
    controller.dispose();
  });
});
