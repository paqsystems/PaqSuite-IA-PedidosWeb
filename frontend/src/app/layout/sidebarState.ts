export const mobileSidebarBreakpointPx = 768;

export function getNextSidebarCollapsed(currentCollapsed: boolean): boolean {
  return !currentCollapsed;
}

export function shouldUseOverlaySidebar(viewportWidth: number): boolean {
  return viewportWidth < mobileSidebarBreakpointPx;
}
