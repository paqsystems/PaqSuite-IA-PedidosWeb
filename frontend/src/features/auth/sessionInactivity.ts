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
