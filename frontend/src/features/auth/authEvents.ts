const authExpiredEventName = 'pedidosweb:auth-expired';
const authenticatedRequestSucceededEventName = 'pedidosweb:authenticated-request-succeeded';

export type AuthExpiredDetail = {
  reasonKey: string;
};

export function dispatchAuthExpired(reasonKey = 'auth.unauthenticated'): void {
  if (typeof window === 'undefined') {
    return;
  }

  window.dispatchEvent(
    new CustomEvent<AuthExpiredDetail>(authExpiredEventName, {
      detail: { reasonKey },
    }),
  );
}

export function dispatchAuthenticatedRequestSucceeded(): void {
  if (typeof window === 'undefined') {
    return;
  }

  window.dispatchEvent(new Event(authenticatedRequestSucceededEventName));
}

export { authExpiredEventName, authenticatedRequestSucceededEventName };
