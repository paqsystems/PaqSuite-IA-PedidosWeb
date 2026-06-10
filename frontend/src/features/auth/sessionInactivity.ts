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

export function shouldTrackInactivityKey(key: string): boolean {
  return key !== 'Tab';
}

export type InactivityController = {
  touch: () => void;
  dispose: () => void;
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
