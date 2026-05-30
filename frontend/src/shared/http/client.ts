const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';
const tenantHeaderName = 'X-Paq-Cliente';
const tenantFallbackValue = import.meta.env.VITE_TENANT_DEFAULT_CLIENT ?? 'desarrollo';

export function getApiBaseUrl(): string {
  return apiBaseUrl;
}

export function buildTenantHeaders(cliente?: string): Record<string, string> {
  return {
    [tenantHeaderName]: cliente ?? tenantFallbackValue,
  };
}

type ApiRequestOptions = RequestInit & {
  skipAuth?: boolean;
};

export async function apiRequest<T>(path: string, options: ApiRequestOptions = {}): Promise<{
  error: number;
  respuesta: string;
  resultado: T;
}> {
  const headers = new Headers(options.headers ?? {});
  const tenantHeaders = buildTenantHeaders();

  Object.entries(tenantHeaders).forEach(([headerName, headerValue]) => {
    headers.set(headerName, headerValue);
  });

  if (!headers.has('Content-Type') && options.body) {
    headers.set('Content-Type', 'application/json');
  }

  if (!options.skipAuth) {
    const token = localStorage.getItem('pedidosweb.auth.token');

    if (token) {
      headers.set('Authorization', `Bearer ${token}`);
    }
  }

  const response = await fetch(`${apiBaseUrl}${path}`, {
    ...options,
    headers,
  });

  const payload = await response.json();

  if (!response.ok) {
    throw new ApiClientError(response.status, payload.respuesta ?? 'request.failed', payload.error ?? response.status);
  }

  return payload;
}

export class ApiClientError extends Error {
  constructor(
    public readonly status: number,
    public readonly respuestaKey: string,
    public readonly errorCode: number,
  ) {
    super(respuestaKey);
  }
}
