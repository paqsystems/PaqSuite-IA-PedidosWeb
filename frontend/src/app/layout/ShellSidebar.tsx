import { MenuSidebarPanel } from '../../features/menu/components/MenuSidebarPanel';
import type { MenuNode } from '../../features/menu/menuApi';
import type { MenuDisplayMode } from '../../features/menu/utils/menuPresentationStorage';

type ShellSidebarProps = {
  menuItems: MenuNode[];
  isLoading: boolean;
  errorKey: string | null;
  isCollapsed: boolean;
  menuTreeExpanded: boolean;
  menuDisplayMode: MenuDisplayMode;
  openInNewTab: boolean;
  onAfterItemNavigate?: () => void;
};

export function ShellSidebar({
  menuItems,
  isLoading,
  errorKey,
  isCollapsed,
  menuTreeExpanded,
  menuDisplayMode,
  openInNewTab,
  onAfterItemNavigate,
}: ShellSidebarProps) {
  if (isCollapsed) {
    return (
      <aside
        className="shellSidebar"
        data-testid="shellSidebar"
        aria-hidden="true"
        hidden
      />
    );
  }

  return (
    <aside className="shellSidebar" data-testid="shellSidebar">
      <MenuSidebarPanel
        menuItems={menuItems}
        isLoading={isLoading}
        errorKey={errorKey}
        menuTreeExpanded={menuTreeExpanded}
        menuDisplayMode={menuDisplayMode}
        openInNewTab={openInNewTab}
        onAfterItemNavigate={onAfterItemNavigate}
      />
    </aside>
  );
}
