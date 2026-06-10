import { describe, expect, it, vi } from 'vitest';
import {
  bindInactivityActivityListeners,
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

  it('considera cualquier tecla incluido Tab como actividad valida', () => {
    expect(shouldTrackInactivityKey('Tab')).toBe(true);
    expect(shouldTrackInactivityKey('Enter')).toBe(true);
    expect(shouldTrackInactivityKey('a')).toBe(true);
  });

  it('bindInactivityActivityListeners detecta click aunque el target detenga propagacion', () => {
    const onActivity = vi.fn();
    const button = document.createElement('button');
    document.body.appendChild(button);

    button.addEventListener('click', (event) => {
      event.stopPropagation();
    });

    const unbind = bindInactivityActivityListeners(onActivity, { target: document });

    button.click();

    expect(onActivity).toHaveBeenCalledTimes(1);

    unbind();
    document.body.removeChild(button);
  });

  it('bindInactivityActivityListeners registra Tab y otras teclas en keydown', () => {
    const onActivity = vi.fn();
    const unbind = bindInactivityActivityListeners(onActivity, { target: document });

    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Tab', bubbles: true }));
    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));

    expect(onActivity).toHaveBeenCalledTimes(2);

    unbind();
  });

  it('bindInactivityActivityListeners registra scroll en contenedores internos', () => {
    const onActivity = vi.fn();
    const container = document.createElement('div');
    container.style.height = '50px';
    container.style.overflow = 'auto';
    const content = document.createElement('div');
    content.style.height = '200px';
    container.appendChild(content);
    document.body.appendChild(container);

    const unbind = bindInactivityActivityListeners(onActivity, { target: document });

    container.scrollTop = 20;
    container.dispatchEvent(new Event('scroll', { bubbles: false }));

    expect(onActivity).toHaveBeenCalledTimes(1);

    unbind();
    document.body.removeChild(container);
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
