import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { UNSAFE_NavigationContext as NavigationContext } from 'react-router-dom';

export type ImportacionMasivaSalidaAccion = 'cancelar' | 'grabarTodo' | 'retornar';

type UseImportacionMasivaNavigationGuardParams = {
  enabled: boolean;
  onSalidaAccion: (accion: ImportacionMasivaSalidaAccion) => void | Promise<void>;
};

/**
 * Guarda de salida compatible con BrowserRouter.
 * useBlocker/unstable_usePrompt exigen data router (createBrowserRouter) y crashean la página.
 */
export function useImportacionMasivaNavigationGuard({
  enabled,
  onSalidaAccion,
}: UseImportacionMasivaNavigationGuardParams) {
  const { navigator } = useContext(NavigationContext);
  const [salidaVisible, setSalidaVisible] = useState(false);
  const pendingProceedRef = useRef<(() => void) | null>(null);
  const enabledRef = useRef(enabled);
  enabledRef.current = enabled;

  useEffect(() => {
    if (!enabled) {
      return undefined;
    }

    const originalPush = navigator.push.bind(navigator);
    const originalReplace = navigator.replace.bind(navigator);

    const requestBlock = (proceed: () => void) => {
      pendingProceedRef.current = proceed;
      setSalidaVisible(true);
    };

    navigator.push = (...args: Parameters<typeof originalPush>) => {
      if (!enabledRef.current) {
        originalPush(...args);
        return;
      }
      requestBlock(() => originalPush(...args));
    };

    navigator.replace = (...args: Parameters<typeof originalReplace>) => {
      if (!enabledRef.current) {
        originalReplace(...args);
        return;
      }
      requestBlock(() => originalReplace(...args));
    };

    return () => {
      navigator.push = originalPush;
      navigator.replace = originalReplace;
    };
  }, [enabled, navigator]);

  useEffect(() => {
    if (!enabled) {
      return undefined;
    }

    const handleBeforeUnload = (event: BeforeUnloadEvent) => {
      event.preventDefault();
      event.returnValue = '';
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [enabled]);

  const closeSalidaModal = useCallback(() => {
    setSalidaVisible(false);
    pendingProceedRef.current = null;
  }, []);

  const confirmSalida = useCallback(
    async (accion: ImportacionMasivaSalidaAccion) => {
      await onSalidaAccion(accion);
      setSalidaVisible(false);

      if (accion === 'retornar') {
        pendingProceedRef.current = null;
        return;
      }

      const proceed = pendingProceedRef.current;
      pendingProceedRef.current = null;
      proceed?.();
    },
    [onSalidaAccion],
  );

  const requestSalida = useCallback(() => {
    if (!enabled) {
      return;
    }
    setSalidaVisible(true);
  }, [enabled]);

  return {
    salidaVisible,
    closeSalidaModal,
    confirmSalida,
    requestSalida,
  };
}
