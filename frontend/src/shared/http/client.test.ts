/** @vitest-environment jsdom */

import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  authExpiredEventName,
  authenticatedRequestSucceededEventName,
} from '../../features/auth/authEvents';
import { ApiClientError, apiRequest } from './client';

describe('apiRequest', () => {
  afterEach(() => {
    localStorage.clear();
    vi.unstubAllGlobals();
  });

  it('dispara evento de sesion expirada ante 401 autenticado', async () => {
    const expiredHandler = vi.fn();
    window.addEventListener(authExpiredEventName, expiredHandler as EventListener);

    vi.stubGlobal(
      'fetch',
      vi.fn(async () =>
        new Response(
          JSON.stringify({
            error: 401,
            respuesta: 'auth.unauthenticated',
            resultado: {},
          }),
          {
            status: 401,
            headers: { 'Content-Type': 'application/json' },
          },
        ),
      ),
    );

    await expect(apiRequest('/auth/me')).rejects.toBeInstanceOf(ApiClientError);
    expect(expiredHandler).toHaveBeenCalledTimes(1);
  });

  it('no dispara sesion expirada para requests publicos con skipAuth', async () => {
    const expiredHandler = vi.fn();
    window.addEventListener(authExpiredEventName, expiredHandler as EventListener);

    vi.stubGlobal(
      'fetch',
      vi.fn(async () =>
        new Response(
          JSON.stringify({
            error: 401,
            respuesta: 'auth.invalidCredentials',
            resultado: {},
          }),
          {
            status: 401,
            headers: { 'Content-Type': 'application/json' },
          },
        ),
      ),
    );

    await expect(apiRequest('/auth/login', { method: 'POST', skipAuth: true })).rejects.toBeInstanceOf(ApiClientError);
    expect(expiredHandler).not.toHaveBeenCalled();
  });

  it('dispara actividad en requests autenticados exitosos', async () => {
    const activityHandler = vi.fn();
    window.addEventListener(authenticatedRequestSucceededEventName, activityHandler as EventListener);

    vi.stubGlobal(
      'fetch',
      vi.fn(async () =>
        new Response(
          JSON.stringify({
            error: 0,
            respuesta: 'ok',
            resultado: {},
          }),
          {
            status: 200,
            headers: { 'Content-Type': 'application/json' },
          },
        ),
      ),
    );

    await expect(apiRequest('/auth/me')).resolves.toEqual({
      error: 0,
      respuesta: 'ok',
      resultado: {},
    });
    expect(activityHandler).toHaveBeenCalledTimes(1);
  });
});
