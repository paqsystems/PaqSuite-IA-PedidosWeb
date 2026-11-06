import { bootstrapApiBaseUrl, clienteHeaderName, resolveRequestUrl } from '@paqsuite/react-core';
import { getStoredToken } from '../auth/authStorage';
import { buildTenantHeaders } from '../../shared/http/client';
import { isNativeApp } from '../../shared/platform/isNativeApp';

/**
 * Inyecta Authorization + tenant en fetch hacia /api/* (GEN EmissionDialog, etc.).
 */
export function installApiAuthFetch(): void {
  if (typeof window === 'undefined') {
    return;
  }

  const flaggedWindow = window as Window & { __paqPedidosWebApiAuthFetch?: boolean };
  if (flaggedWindow.__paqPedidosWebApiAuthFetch) {
    return;
  }

  const originalFetch = window.fetch.bind(window);

  window.fetch = async (input: RequestInfo | URL, init?: RequestInit): Promise<Response> => {
    const url =
      typeof input === 'string'
        ? input
        : input instanceof URL
          ? input.toString()
          : input.url;

    if (!url.includes('/api/')) {
      return originalFetch(input, init);
    }

    const resolvedUrl = resolveRequestUrl(url);
    const fetchInput =
      typeof input === 'string'
        ? resolvedUrl
        : input instanceof URL
          ? new URL(resolvedUrl)
          : new Request(resolvedUrl, input);

    const headers = new Headers(init?.headers ?? (input instanceof Request ? input.headers : undefined));

    if (!headers.has('Accept')) {
      headers.set('Accept', 'application/json');
    }

    const token = getStoredToken();
    if (token && !headers.has('Authorization')) {
      headers.set('Authorization', `Bearer ${token}`);
    }

    const tenantHeaders = buildTenantHeaders();
    if (!headers.has(clienteHeaderName) && tenantHeaders['X-Paq-Cliente']) {
      headers.set(clienteHeaderName, tenantHeaders['X-Paq-Cliente']);
    }

    return originalFetch(fetchInput, { ...init, headers });
  };

  flaggedWindow.__paqPedidosWebApiAuthFetch = true;
}

export async function bootstrapPedidosWebApiBase(): Promise<void> {
  await bootstrapApiBaseUrl({
    envBaseUrl: import.meta.env.VITE_API_BASE_URL ?? '/api/v1',
    projectSlug: 'pedidosweb',
    isNative: isNativeApp(),
  });
}
