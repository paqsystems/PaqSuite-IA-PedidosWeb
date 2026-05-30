export const menuAppId = 'pedidosweb';

export type MenuDisplayMode = 'allBranches' | 'operationalOnly';

export type MenuPresentationState = {
  sidebarVisible: boolean;
  menuTreeExpanded: boolean;
  menuDisplayMode: MenuDisplayMode;
};

export const defaultMenuPresentationState: MenuPresentationState = {
  sidebarVisible: true,
  menuTreeExpanded: true,
  menuDisplayMode: 'allBranches',
};

export function buildMenuPresentationStorageKey(
  userId: number,
  suffix: 'sidebarVisible' | 'treeExpanded' | 'displayMode',
): string {
  return `${menuAppId}.${userId}.menu.${suffix}`;
}

export function readMenuPresentationState(userId: number): MenuPresentationState {
  if (typeof window === 'undefined') {
    return defaultMenuPresentationState;
  }

  const sidebarVisibleRaw = localStorage.getItem(
    buildMenuPresentationStorageKey(userId, 'sidebarVisible'),
  );
  const menuTreeExpandedRaw = localStorage.getItem(
    buildMenuPresentationStorageKey(userId, 'treeExpanded'),
  );
  const menuDisplayModeRaw = localStorage.getItem(
    buildMenuPresentationStorageKey(userId, 'displayMode'),
  );

  return {
    sidebarVisible: sidebarVisibleRaw === null ? true : sidebarVisibleRaw === 'true',
    menuTreeExpanded: menuTreeExpandedRaw === null ? true : menuTreeExpandedRaw === 'true',
    menuDisplayMode: menuDisplayModeRaw === 'operationalOnly' ? 'operationalOnly' : 'allBranches',
  };
}

export function writeMenuPresentationState(
  userId: number,
  state: MenuPresentationState,
): void {
  if (typeof window === 'undefined') {
    return;
  }

  localStorage.setItem(
    buildMenuPresentationStorageKey(userId, 'sidebarVisible'),
    String(state.sidebarVisible),
  );
  localStorage.setItem(
    buildMenuPresentationStorageKey(userId, 'treeExpanded'),
    String(state.menuTreeExpanded),
  );
  localStorage.setItem(
    buildMenuPresentationStorageKey(userId, 'displayMode'),
    state.menuDisplayMode,
  );
}
