import { useCallback, useEffect, useState } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import {
  defaultMenuPresentationState,
  readMenuPresentationState,
  type MenuDisplayMode,
  type MenuPresentationState,
  writeMenuPresentationState,
} from '../utils/menuPresentationStorage';

function resolveInitialMenuState(userId: number | null): MenuPresentationState {
  if (userId === null) {
    return {
      ...defaultMenuPresentationState,
      sidebarVisible: !isNativeApp(),
    };
  }

  return readMenuPresentationState(userId);
}

export function useMenuPresentation(userId: number | null) {
  const [state, setState] = useState<MenuPresentationState>(() => resolveInitialMenuState(userId));

  useEffect(() => {
    if (userId === null) {
      setState({
        ...defaultMenuPresentationState,
        sidebarVisible: !isNativeApp(),
      });
      return;
    }

    setState(readMenuPresentationState(userId));
  }, [userId]);

  const persistState = useCallback(
    (updater: MenuPresentationState | ((current: MenuPresentationState) => MenuPresentationState)) => {
      setState((current) => {
        const nextState = typeof updater === 'function' ? updater(current) : updater;

        if (userId !== null) {
          writeMenuPresentationState(userId, nextState);
        }

        return nextState;
      });
    },
    [userId],
  );

  const toggleSidebarVisible = useCallback(() => {
    persistState((current) => ({
      ...current,
      sidebarVisible: !current.sidebarVisible,
    }));
  }, [persistState]);

  const closeSidebarVisible = useCallback(() => {
    persistState((current) => {
      if (!current.sidebarVisible) {
        return current;
      }

      return {
        ...current,
        sidebarVisible: false,
      };
    });
  }, [persistState]);

  const toggleMenuTreeExpanded = useCallback(() => {
    persistState((current) => ({
      ...current,
      menuTreeExpanded: !current.menuTreeExpanded,
    }));
  }, [persistState]);

  const toggleMenuDisplayMode = useCallback(() => {
    persistState((current) => {
      const nextMode: MenuDisplayMode =
        current.menuDisplayMode === 'allBranches' ? 'operationalOnly' : 'allBranches';

      return {
        ...current,
        menuDisplayMode: nextMode,
      };
    });
  }, [persistState]);

  return {
    sidebarVisible: state.sidebarVisible,
    menuTreeExpanded: state.menuTreeExpanded,
    menuDisplayMode: state.menuDisplayMode,
    toggleSidebarVisible,
    closeSidebarVisible,
    toggleMenuTreeExpanded,
    toggleMenuDisplayMode,
  };
}
