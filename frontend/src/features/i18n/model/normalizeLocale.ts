import { defaultLocale, isSupportedLocale } from './supportedLocales';

export function normalizeLocale(value: string | null | undefined): string | null {
  if (value === null || value === undefined || value.trim() === '') {
    return null;
  }

  const catalogCode = value.trim().split('-')[0]?.toLowerCase();

  if (catalogCode === undefined || catalogCode === '' || !isSupportedLocale(catalogCode)) {
    return null;
  }

  return catalogCode;
}

export function normalizeLocaleWithFallback(
  value: string | null | undefined,
  fallback?: string | null,
): string {
  const normalized = normalizeLocale(value);

  if (normalized !== null) {
    return normalized;
  }

  const fallbackNormalized = fallback !== undefined ? normalizeLocale(fallback) : null;

  if (fallbackNormalized !== null) {
    return fallbackNormalized;
  }

  return defaultLocale;
}
