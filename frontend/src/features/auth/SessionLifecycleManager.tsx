import { useEffect } from 'react';
import { useAuth } from './AuthProvider';
import { authExpiredEventName } from './authEvents';
import type { AuthExpiredDetail } from './authEvents';
import { useInactivityTimeout } from './useInactivityTimeout';

export function SessionLifecycleManager() {
  const { isAuthenticated, sessionContext, expireSession } = useAuth();

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

  useInactivityTimeout({
    enabled: isAuthenticated && sessionContext !== null,
    inactivityTimeoutMinutes: sessionContext?.inactivityTimeoutMinutes,
    onExpire: () => {
      void expireSession({ reasonKey: 'auth.unauthenticated', revokeToken: true });
    },
  });

  return null;
}
