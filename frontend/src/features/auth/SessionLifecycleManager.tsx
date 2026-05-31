import { useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import { useAuth } from './AuthProvider';
import { authExpiredEventName, authenticatedRequestSucceededEventName } from './authEvents';
import type { AuthExpiredDetail } from './authEvents';
import { resolveInactivityTimeoutMs, shouldTrackInactivityKey } from './sessionInactivity';

export function SessionLifecycleManager() {
  const location = useLocation();
  const { isAuthenticated, sessionContext, expireSession } = useAuth();
  const timeoutIdRef = useRef<number | null>(null);
  const isExpiringRef = useRef(false);

  useEffect(() => {
    function handleAuthExpired(event: Event) {
      const customEvent = event as CustomEvent<AuthExpiredDetail>;
      const reasonKey = customEvent.detail?.reasonKey ?? 'auth.unauthenticated';
      void expireSession({ reasonKey, revokeToken: false });
    }

    window.addEventListener(authExpiredEventName, handleAuthExpired as EventListener);

    return () => {
      window.removeEventListener(authExpiredEventName, handleAuthExpired as EventListener);
    };
  }, [expireSession]);

  useEffect(() => {
    if (!isAuthenticated || sessionContext === null) {
      isExpiringRef.current = false;

      if (timeoutIdRef.current !== null) {
        window.clearTimeout(timeoutIdRef.current);
        timeoutIdRef.current = null;
      }

      return;
    }

    const timeoutMs = resolveInactivityTimeoutMs(sessionContext.inactivityTimeoutMinutes);

    function scheduleExpiration() {
      if (timeoutIdRef.current !== null) {
        window.clearTimeout(timeoutIdRef.current);
      }

      timeoutIdRef.current = window.setTimeout(() => {
        if (isExpiringRef.current) {
          return;
        }

        isExpiringRef.current = true;
        void expireSession({ reasonKey: 'auth.unauthenticated', revokeToken: true });
      }, timeoutMs);
    }

    function handlePointerActivity() {
      scheduleExpiration();
    }

    function handleKeyboardActivity(event: KeyboardEvent) {
      if (!shouldTrackInactivityKey(event.key)) {
        return;
      }

      scheduleExpiration();
    }

    function handleAuthenticatedRequestSucceeded() {
      scheduleExpiration();
    }

    isExpiringRef.current = false;
    scheduleExpiration();

    window.addEventListener('pointerdown', handlePointerActivity);
    window.addEventListener('keydown', handleKeyboardActivity);
    window.addEventListener(
      authenticatedRequestSucceededEventName,
      handleAuthenticatedRequestSucceeded as EventListener,
    );

    return () => {
      window.removeEventListener('pointerdown', handlePointerActivity);
      window.removeEventListener('keydown', handleKeyboardActivity);
      window.removeEventListener(
        authenticatedRequestSucceededEventName,
        handleAuthenticatedRequestSucceeded as EventListener,
      );

      if (timeoutIdRef.current !== null) {
        window.clearTimeout(timeoutIdRef.current);
        timeoutIdRef.current = null;
      }
    };
  }, [expireSession, isAuthenticated, location.pathname, sessionContext]);

  return null;
}
