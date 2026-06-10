import { describe, expect, it } from 'vitest';
import {
  getNextSidebarCollapsed,
  shouldUseOverlaySidebar,
} from './sidebarState';

describe('sidebarState', () => {
  it('alterna el estado colapsado del sidebar', () => {
    expect(getNextSidebarCollapsed(false)).toBe(true);
    expect(getNextSidebarCollapsed(true)).toBe(false);
  });

  it('usa overlay en viewports reducidos', () => {
    expect(shouldUseOverlaySidebar(767)).toBe(true);
    expect(shouldUseOverlaySidebar(768)).toBe(false);
  });
});
