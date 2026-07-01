import { Preferences } from '@capacitor/preferences';

const storagePrefix = 'pedidosweb.mobile';

export const mobilePreferenceKeys = {
  activeTenant: `${storagePrefix}.tenant`,
  lastTenant: `${storagePrefix}.lastTenant`,
  apiBaseUrlOverride: `${storagePrefix}.apiBaseUrlOverride`,
} as const;

export type MobilePreferenceSnapshot = {
  activeTenant: string | null;
  lastTenant: string | null;
  apiBaseUrlOverride: string | null;
};

export async function readMobilePreferences(): Promise<MobilePreferenceSnapshot> {
  const [activeTenant, lastTenant, apiBaseUrlOverride] = await Promise.all([
    Preferences.get({ key: mobilePreferenceKeys.activeTenant }),
    Preferences.get({ key: mobilePreferenceKeys.lastTenant }),
    Preferences.get({ key: mobilePreferenceKeys.apiBaseUrlOverride }),
  ]);

  return {
    activeTenant: activeTenant.value ?? null,
    lastTenant: lastTenant.value ?? null,
    apiBaseUrlOverride: apiBaseUrlOverride.value ?? null,
  };
}

export async function writeMobilePreference(key: string, value: string): Promise<void> {
  await Preferences.set({ key, value });
}

export async function removeMobilePreference(key: string): Promise<void> {
  await Preferences.remove({ key });
}
