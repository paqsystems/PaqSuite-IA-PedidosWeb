const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api/v1';
const tenantHeaderName = 'X-Paq-Cliente';
const tenantFallbackValue = 'demo';

export function getApiBaseUrl(): string {
  return apiBaseUrl;
}

export function buildTenantHeaders(cliente?: string): Record<string, string> {
  return {
    [tenantHeaderName]: cliente ?? tenantFallbackValue
  };
}
