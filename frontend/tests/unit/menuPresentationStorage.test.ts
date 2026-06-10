import { beforeEach, describe, expect, it } from 'vitest';
import {
  buildMenuPresentationStorageKey,
  readMenuPresentationState,
  writeMenuPresentationState,
} from '../../src/features/menu/utils/menuPresentationStorage';

describe('menuPresentationStorage', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  it('usa claves por appId y userId', () => {
    expect(buildMenuPresentationStorageKey(42, 'displayMode')).toBe(
      'pedidosweb.42.menu.displayMode',
    );
  });

  it('persiste y restaura estado por usuario', () => {
    writeMenuPresentationState(7, {
      sidebarVisible: false,
      menuTreeExpanded: false,
      menuDisplayMode: 'operationalOnly',
    });

    expect(readMenuPresentationState(7)).toEqual({
      sidebarVisible: false,
      menuTreeExpanded: false,
      menuDisplayMode: 'operationalOnly',
    });
  });

  it('no mezcla estado entre usuarios distintos', () => {
    writeMenuPresentationState(1, {
      sidebarVisible: false,
      menuTreeExpanded: true,
      menuDisplayMode: 'operationalOnly',
    });

    expect(readMenuPresentationState(2).sidebarVisible).toBe(true);
  });
});
