import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { ApiClientError } from '../../shared/http/client';
import { logoutRequest, meRequest } from './authApi';
import {
  clearAuthSession,
  consumeExpiredReason,
  storeExpiredReason,
  getStoredSessionContext,
  getStoredToken,
  updateStoredSessionContext,
} from './authStorage';
import type { SessionContext } from './types';
import { clearImportacionMasivaBorradorStorage } from '../pedidos/importacionMasiva/utils/importacionMasivaBorradorStorage';
import { clearClientesCache } from '../pedidos/api/comprobanteApi';

type AuthContextValue = {
  sessionContext: SessionContext | null;
  isBootstrapping: boolean;
  isAuthenticated: boolean;
  setSessionContext: (sessionContext: SessionContext | null) => void;
  expiredReasonKey: string | null;
  clearExpiredReason: () => void;
  expireSession: (options?: { reasonKey?: string; revokeToken?: boolean }) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | null>(null);

type AuthProviderProps = {
  children: React.ReactNode;
};

export function AuthProvider({ children }: AuthProviderProps) {
  const [sessionContext, setSessionContext] = useState<SessionContext | null>(() => getStoredSessionContext());
  const [isBootstrapping, setIsBootstrapping] = useState(() => getStoredToken() !== null);
  const [expiredReasonKey, setExpiredReasonKey] = useState<string | null>(() => consumeExpiredReason());

  const clearSession = useCallback((reasonKey?: string) => {
    clearImportacionMasivaBorradorStorage();
    clearClientesCache();

    if (reasonKey) {
      storeExpiredReason(reasonKey);
      setExpiredReasonKey(reasonKey);
    } else {
      setExpiredReasonKey(null);
    }

    void clearAuthSession().finally(() => {
      setSessionContext(null);
      setIsBootstrapping(false);
    });
  }, []);

  const clearExpiredReason = useCallback(() => {
    consumeExpiredReason();
    setExpiredReasonKey(null);
  }, []);

  const expireSession = useCallback(
    async (options?: { reasonKey?: string; revokeToken?: boolean }) => {
      const reasonKey = options?.reasonKey ?? 'auth.unauthenticated';
      const revokeToken = options?.revokeToken ?? false;
      const hasToken = getStoredToken() !== null;

      if (revokeToken && hasToken) {
        try {
          await logoutRequest();
        } catch {
          // Si la revocacion falla igual dejamos al usuario fuera de la sesion local.
        }
      }

      clearSession(reasonKey);
    },
    [clearSession],
  );

  useEffect(() => {
    const token = getStoredToken();

    if (!token) {
      setIsBootstrapping(false);
      return;
    }

    let isCancelled = false;

    async function bootstrapSession() {
      try {
        const envelope = await meRequest();

        if (!isCancelled) {
          updateStoredSessionContext(envelope.resultado);
          setSessionContext(envelope.resultado);
        }
      } catch (error) {
        if (!isCancelled) {
          if (error instanceof ApiClientError && error.status === 401) {
            clearSession(error.respuestaKey);
          } else {
            clearSession();
          }
        }
      } finally {
        if (!isCancelled) {
          setIsBootstrapping(false);
        }
      }
    }

    bootstrapSession();

    return () => {
      isCancelled = true;
    };
  }, []);

  const logout = useCallback(async () => {
    try {
      await logoutRequest();
    } catch {
      // Si el token ya expiro, igual limpiamos la sesion local.
    }

    clearSession();
  }, [clearSession]);

  const value = useMemo<AuthContextValue>(
    () => ({
      sessionContext,
      isBootstrapping,
      isAuthenticated: sessionContext !== null,
      setSessionContext,
      expiredReasonKey,
      clearExpiredReason,
      expireSession,
      logout,
    }),
    [clearExpiredReason, expireSession, expiredReasonKey, isBootstrapping, logout, sessionContext],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);

  if (context === null) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }

  return context;
}

export function useRequiredSessionContext(): SessionContext {
  const { sessionContext } = useAuth();

  if (sessionContext === null) {
    throw new Error('Se requiere sesion autenticada');
  }

  return sessionContext;
}
