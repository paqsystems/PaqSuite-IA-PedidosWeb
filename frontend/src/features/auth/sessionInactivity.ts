const defaultInactivityTimeoutMinutes = 10;

export function resolveInactivityTimeoutMinutes(inactivityTimeoutMinutes: number | null | undefined): number {
  if (typeof inactivityTimeoutMinutes !== 'number' || Number.isNaN(inactivityTimeoutMinutes)) {
    return defaultInactivityTimeoutMinutes;
  }

  return inactivityTimeoutMinutes > 0 ? inactivityTimeoutMinutes : defaultInactivityTimeoutMinutes;
}

export function resolveInactivityTimeoutMs(inactivityTimeoutMinutes: number | null | undefined): number {
  return resolveInactivityTimeoutMinutes(inactivityTimeoutMinutes) * 60 * 1000;
}

export function shouldTrackInactivityKey(_key: string): boolean {
  return true;
}

export type InactivityController = {
  touch: () => void;
  dispose: () => void;
};

/** Eventos DOM que reinician el temporizador (fase capture para widgets DevExtreme). */
export const inactivityDomActivityEvents = [
  'pointerdown',
  'click',
  'touchstart',
  'wheel',
  'scroll',
  'input',
] as const;

type BindInactivityActivityListenersOptions = {
  target?: EventTarget;
};

export function bindInactivityActivityListeners(
  onActivity: () => void,
  options: BindInactivityActivityListenersOptions = {},
): () => void {
  const target = options.target ?? document;
  const listenerOptions: AddEventListenerOptions = { capture: true };

  const handleGenericActivity = () => {
    onActivity();
  };

  const handleKeydown = (event: Event) => {
    const keyboardEvent = event as KeyboardEvent;

    if (!shouldTrackInactivityKey(keyboardEvent.key)) {
      return;
    }

    onActivity();
  };

  inactivityDomActivityEvents.forEach((eventName) => {
    target.addEventListener(eventName, handleGenericActivity, listenerOptions);
  });
  target.addEventListener('keydown', handleKeydown, listenerOptions);

  return () => {
    inactivityDomActivityEvents.forEach((eventName) => {
      target.removeEventListener(eventName, handleGenericActivity, listenerOptions);
    });
    target.removeEventListener('keydown', handleKeydown, listenerOptions);
  };
};

export function createInactivityController(timeoutMs: number, onExpire: () => void): InactivityController {
  let lastActivityAt = Date.now();
  let timeoutId: number | null = null;
  let isExpiring = false;

  const clearScheduledExpiration = () => {
    if (timeoutId !== null) {
      window.clearTimeout(timeoutId);
      timeoutId = null;
    }
  };

  const triggerExpiration = () => {
    if (isExpiring) {
      return;
    }

    isExpiring = true;
    onExpire();
  };

  const scheduleExpiration = () => {
    clearScheduledExpiration();

    const elapsed = Date.now() - lastActivityAt;
    const remaining = timeoutMs - elapsed;

    if (remaining <= 0) {
      triggerExpiration();

      return;
    }

    timeoutId = window.setTimeout(() => {
      if (Date.now() - lastActivityAt >= timeoutMs) {
        triggerExpiration();

        return;
      }

      scheduleExpiration();
    }, remaining);
  };

  const touch = () => {
    lastActivityAt = Date.now();
    isExpiring = false;
    scheduleExpiration();
  };

  touch();

  return {
    touch,
    dispose: clearScheduledExpiration,
  };
}
