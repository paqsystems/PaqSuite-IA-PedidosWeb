import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { logoutRequest, meRequest } from './authApi';
import {
  clearAuthSession,
  getStoredSessionContext,
  getStoredToken,
  updateStoredSessionContext,
} from './authStorage';
import type { SessionContext } from './types';

type AuthContextValue = {
  sessionContext: SessionContext | null;
  isBootstrapping: boolean;
  isAuthenticated: boolean;
  setSessionContext: (sessionContext: SessionContext | null) => void;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | null>(null);

type AuthProviderProps = {
  children: React.ReactNode;
};

export function AuthProvider({ children }: AuthProviderProps) {
  const [sessionContext, setSessionContext] = useState<SessionContext | null>(() => getStoredSessionContext());
  const [isBootstrapping, setIsBootstrapping] = useState(() => getStoredToken() !== null);

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
      } catch {
        if (!isCancelled) {
          clearAuthSession();
          setSessionContext(null);
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

    clearAuthSession();
    setSessionContext(null);
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      sessionContext,
      isBootstrapping,
      isAuthenticated: sessionContext !== null,
      setSessionContext,
      logout,
    }),
    [isBootstrapping, logout, sessionContext],
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
