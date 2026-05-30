import { useEffect, useState } from 'react';
import { Outlet } from 'react-router-dom';
import { useAuth, useRequiredSessionContext } from '../../features/auth/AuthProvider';
import { useUserMenu } from '../../features/menu/useUserMenu';
import { useMenuPresentation } from '../../features/menu/hooks/useMenuPresentation';
import { useUserPreferences } from '../../features/preferences/useUserPreferences';
import { ShellFooter } from './ShellFooter';
import { ShellHeader } from './ShellHeader';
import { ShellSidebar } from './ShellSidebar';
import { shouldUseOverlaySidebar } from './sidebarState';
import './shellLayout.css';

export function ShellLayout() {
  const sessionContext = useRequiredSessionContext();
  const { logout } = useAuth();
  const { menuItems, isLoading, errorKey } = useUserMenu(true);
  const menuPresentation = useMenuPresentation(sessionContext.user.id);
  const { preferences, isSavingOpenInNewTab, updateOpenInNewTab } = useUserPreferences(sessionContext);
  const [isOverlayMode, setIsOverlayMode] = useState(() =>
    typeof window !== 'undefined' ? shouldUseOverlaySidebar(window.innerWidth) : false,
  );

  useEffect(() => {
    function handleResize() {
      setIsOverlayMode(shouldUseOverlaySidebar(window.innerWidth));
    }

    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
    };
  }, []);

  const layoutClassNames = [
    'shellLayout',
    !isOverlayMode && !menuPresentation.sidebarVisible ? 'shellLayoutSidebarCollapsed' : '',
    isOverlayMode ? 'shellLayoutSidebarOverlay' : '',
    isOverlayMode && menuPresentation.sidebarVisible ? 'shellLayoutSidebarOpen' : '',
  ]
    .filter(Boolean)
    .join(' ');

  const sidebarCollapsed = !menuPresentation.sidebarVisible;

  return (
    <div className={layoutClassNames}>
      <ShellHeader
        sessionContext={sessionContext}
        menuPresentation={menuPresentation}
        openInNewTab={preferences.openInNewTab}
        isSavingOpenInNewTab={isSavingOpenInNewTab}
        onOpenInNewTabChange={(openInNewTab) => {
          void updateOpenInNewTab(openInNewTab);
        }}
        onLogout={logout}
      />
      <ShellSidebar
        menuItems={menuItems}
        isLoading={isLoading}
        errorKey={errorKey}
        isCollapsed={sidebarCollapsed}
        menuTreeExpanded={menuPresentation.menuTreeExpanded}
        menuDisplayMode={menuPresentation.menuDisplayMode}
        openInNewTab={preferences.openInNewTab}
      />
      <main className="shellMain" data-testid="shellMain">
        <Outlet />
      </main>
      <ShellFooter userLabel={sessionContext.user.displayName} />
    </div>
  );
}
