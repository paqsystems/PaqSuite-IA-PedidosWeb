import { useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import { authenticatedRequestSucceededEventName } from './authEvents';
import { createInactivityController, resolveInactivityTimeoutMs, shouldTrackInactivityKey } from './sessionInactivity';

type UseInactivityTimeoutParams = {
  enabled: boolean;
  inactivityTimeoutMinutes: number | null | undefined;
  onExpire: () => void;
};

export function useInactivityTimeout({
  enabled,
  inactivityTimeoutMinutes,
  onExpire,
}: UseInactivityTimeoutParams): void {
  const location = useLocation();
  const onExpireRef = useRef(onExpire);
  const controllerRef = useRef<ReturnType<typeof createInactivityController> | null>(null);

  useEffect(() => {
    onExpireRef.current = onExpire;
  }, [onExpire]);

  useEffect(() => {
    if (!enabled) {
      controllerRef.current?.dispose();
      controllerRef.current = null;

      return;
    }

    const timeoutMs = resolveInactivityTimeoutMs(inactivityTimeoutMinutes);
    const controller = createInactivityController(timeoutMs, () => {
      onExpireRef.current();
    });
    controllerRef.current = controller;

    const handleKeyboardActivity = (event: KeyboardEvent) => {
      if (!shouldTrackInactivityKey(event.key)) {
        return;
      }

      controller.touch();
    };

    const handleAuthenticatedRequestSucceeded = () => {
      controller.touch();
    };

    window.addEventListener('pointerdown', controller.touch);
    window.addEventListener('keydown', handleKeyboardActivity);
    window.addEventListener(
      authenticatedRequestSucceededEventName,
      handleAuthenticatedRequestSucceeded as EventListener,
    );

    return () => {
      window.removeEventListener('pointerdown', controller.touch);
      window.removeEventListener('keydown', handleKeyboardActivity);
      window.removeEventListener(
        authenticatedRequestSucceededEventName,
        handleAuthenticatedRequestSucceeded as EventListener,
      );
      controller.dispose();
      controllerRef.current = null;
    };
  }, [enabled, inactivityTimeoutMinutes]);

  useEffect(() => {
    if (!enabled) {
      return;
    }

    controllerRef.current?.touch();
  }, [enabled, location.pathname]);
}
