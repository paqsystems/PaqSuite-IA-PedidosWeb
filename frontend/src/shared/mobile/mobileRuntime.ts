import { StatusBar } from '@capacitor/status-bar';
import { isNativeApp } from '../platform/isNativeApp';
import {
  mobilePreferenceKeys,
  readMobilePreferences,
  removeMobilePreference,
  writeMobilePreference,
} from './mobilePreferences';
import { normalizeTenant } from './normalizeTenant';

const defaultWebApiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';
const defaultWebTenant = import.meta.env.VITE_TENANT_DEFAULT_CLIENT ?? 'desarrollo';
const defaultNativeApiBaseUrl =
  import.meta.env.VITE_MOBILE_API_BASE_URL ?? 'https://backend.pedidosweb.paqsystems.com/api/v1';

let cachedApiBaseUrl: string | null = null;
let cachedTenant: string | null = null;

/** Barras normales en URL; en Windows a veces se pegan backslashes por error. */
export function normalizeApiBaseUrl(apiBaseUrl: string): string {
  return apiBaseUrl
    .trim()
    .replace(/\\/g, '/')
    .replace(/^(https?):\/(?!\/)/, '$1://')
    .replace(/\/+$/, '');
}

function resolveApiBaseUrl(apiOverride: string | null): string {
  if (apiOverride && apiOverride.trim().length > 0) {
    return normalizeApiBaseUrl(apiOverride);
  }

  if (isNativeApp()) {
    return defaultNativeApiBaseUrl.replace(/\/+$/, '');
  }

  return defaultWebApiBaseUrl.replace(/\/+$/, '');
}

export async function bootstrapMobileRuntime(): Promise<void> {
  if (!isNativeApp()) {
    cachedApiBaseUrl = resolveApiBaseUrl(null);
    cachedTenant = defaultWebTenant;
    return;
  }

  const snapshot = await readMobilePreferences();
  cachedTenant = snapshot.activeTenant ?? snapshot.lastTenant ?? null;
  cachedApiBaseUrl = resolveApiBaseUrl(snapshot.apiBaseUrlOverride);

  try {
    await StatusBar.setOverlaysWebView({ overlay: false });
  } catch {
    // StatusBar no disponible fuera de plataforma nativa
  }
}

export function getApiBaseUrlSync(): string {
  return cachedApiBaseUrl ?? resolveApiBaseUrl(null);
}

export function getActiveTenantSync(): string {
  return cachedTenant ?? defaultWebTenant;
}

export async function setActiveTenant(tenant: string): Promise<void> {
  const normalizedTenant = normalizeTenant(tenant);
  cachedTenant = normalizedTenant;

  if (!isNativeApp()) {
    return;
  }

  await Promise.all([
    writeMobilePreference(mobilePreferenceKeys.activeTenant, normalizedTenant),
    writeMobilePreference(mobilePreferenceKeys.lastTenant, normalizedTenant),
  ]);
}

export async function clearActiveTenant(): Promise<void> {
  if (!isNativeApp()) {
    cachedTenant = defaultWebTenant;
    return;
  }

  const snapshot = await readMobilePreferences();
  cachedTenant = snapshot.lastTenant;

  await removeMobilePreference(mobilePreferenceKeys.activeTenant);
}

export async function getLastTenantForLogin(): Promise<string> {
  if (!isNativeApp()) {
    return defaultWebTenant;
  }

  const snapshot = await readMobilePreferences();
  return snapshot.lastTenant ?? snapshot.activeTenant ?? '';
}

export async function setApiBaseUrlOverride(apiBaseUrl: string): Promise<void> {
  const trimmedUrl = normalizeApiBaseUrl(apiBaseUrl);

  if (!isNativeApp()) {
    return;
  }

  if (trimmedUrl.length === 0) {
    await removeMobilePreference(mobilePreferenceKeys.apiBaseUrlOverride);
    cachedApiBaseUrl = resolveApiBaseUrl(null);
    return;
  }

  await writeMobilePreference(mobilePreferenceKeys.apiBaseUrlOverride, trimmedUrl);
  cachedApiBaseUrl = trimmedUrl;
}

export async function getApiBaseUrlOverride(): Promise<string> {
  if (!isNativeApp()) {
    return '';
  }

  const snapshot = await readMobilePreferences();
  return snapshot.apiBaseUrlOverride ?? '';
}
