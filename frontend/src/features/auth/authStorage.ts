import type { SessionContext } from './types';

const tokenStorageKey = 'pedidosweb.auth.token';
const sessionStorageKey = 'pedidosweb.auth.session';
const expiredReasonStorageKey = 'pedidosweb.auth.expiredReason';

export function getStoredToken(): string | null {
  return localStorage.getItem(tokenStorageKey);
}

export function getStoredSessionContext(): SessionContext | null {
  const rawValue = localStorage.getItem(sessionStorageKey);

  if (!rawValue) {
    return null;
  }

  try {
    return JSON.parse(rawValue) as SessionContext;
  } catch {
    return null;
  }
}

import { clearActiveTenant } from '../../shared/mobile/mobileRuntime';

export async function persistAuthSession(token: string, sessionContext: SessionContext): Promise<void> {
  const { token: _ignoredToken, ...contextWithoutToken } = sessionContext;

  localStorage.setItem(tokenStorageKey, token);
  localStorage.setItem(sessionStorageKey, JSON.stringify(contextWithoutToken));
  sessionStorage.removeItem(expiredReasonStorageKey);
}

export async function clearAuthSession(): Promise<void> {
  localStorage.removeItem(tokenStorageKey);
  localStorage.removeItem(sessionStorageKey);
  await clearActiveTenant();
}

export function updateStoredSessionContext(sessionContext: SessionContext): void {
  const { token: _ignoredToken, ...contextWithoutToken } = sessionContext;
  localStorage.setItem(sessionStorageKey, JSON.stringify(contextWithoutToken));
}

export function storeExpiredReason(reasonKey: string): void {
  sessionStorage.setItem(expiredReasonStorageKey, reasonKey);
}

export function consumeExpiredReason(): string | null {
  const reasonKey = sessionStorage.getItem(expiredReasonStorageKey);

  if (reasonKey) {
    sessionStorage.removeItem(expiredReasonStorageKey);
  }

  return reasonKey;
}
