import { useCallback, useEffect, useState } from 'react';
import {
  defaultMenuPresentationState,
  readMenuPresentationState,
  type MenuDisplayMode,
  type MenuPresentationState,
  writeMenuPresentationState,
} from '../utils/menuPresentationStorage';

export function useMenuPresentation(userId: number | null) {
  const [state, setState] = useState<MenuPresentationState>(defaultMenuPresentationState);

  useEffect(() => {
    if (userId === null) {
      setState(defaultMenuPresentationState);
      return;
    }

    setState(readMenuPresentationState(userId));
  }, [userId]);

  const persistState = useCallback(
    (nextState: MenuPresentationState) => {
      setState(nextState);

      if (userId !== null) {
        writeMenuPresentationState(userId, nextState);
      }
    },
    [userId],
  );

  const toggleSidebarVisible = useCallback(() => {
    persistState({
      ...state,
      sidebarVisible: !state.sidebarVisible,
    });
  }, [persistState, state]);

  const toggleMenuTreeExpanded = useCallback(() => {
    persistState({
      ...state,
      menuTreeExpanded: !state.menuTreeExpanded,
    });
  }, [persistState, state]);

  const toggleMenuDisplayMode = useCallback(() => {
    const nextMode: MenuDisplayMode =
      state.menuDisplayMode === 'allBranches' ? 'operationalOnly' : 'allBranches';

    persistState({
      ...state,
      menuDisplayMode: nextMode,
    });
  }, [persistState, state]);

  return {
    sidebarVisible: state.sidebarVisible,
    menuTreeExpanded: state.menuTreeExpanded,
    menuDisplayMode: state.menuDisplayMode,
    toggleSidebarVisible,
    toggleMenuTreeExpanded,
    toggleMenuDisplayMode,
  };
}
