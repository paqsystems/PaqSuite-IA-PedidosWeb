import { useEffect, useMemo, useRef, useState } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth, useRequiredSessionContext } from '../../features/auth/AuthProvider';
import { MobileRouteGuard } from '../../features/mobile/MobileRouteGuard';
import { filterMenuTreeForMobileV2 } from '../../features/mobile/pedidosWebMobilePolicy';
import { useUserMenu } from '../../features/menu/useUserMenu';
import { useMenuPresentation } from '../../features/menu/hooks/useMenuPresentation';
import { useUserPreferences } from '../../features/preferences/useUserPreferences';
import { isNativeApp } from '../../shared/platform/isNativeApp';
import { ShellFooter } from './ShellFooter';
import { ShellHeader } from './ShellHeader';
import { ShellSidebar } from './ShellSidebar';
import { shouldUseOverlaySidebar } from './sidebarState';
import './shellLayout.css';

export function ShellLayout() {
  const sessionContext = useRequiredSessionContext();
  const location = useLocation();
  const { t } = useTranslation();
  const { logout } = useAuth();
  const nativeApp = isNativeApp();
  const { menuItems, isLoading, errorKey } = useUserMenu(true);
  const filteredMenuItems = useMemo(
    () => (nativeApp ? filterMenuTreeForMobileV2(menuItems) : menuItems),
    [menuItems, nativeApp],
  );
  const menuPresentation = useMenuPresentation(sessionContext.user.id);
  const { preferences, isSavingOpenInNewTab, updateOpenInNewTab } = useUserPreferences(sessionContext);
  const [isOverlayMode, setIsOverlayMode] = useState(() =>
    nativeApp || (typeof window !== 'undefined' ? shouldUseOverlaySidebar(window.innerWidth) : false),
  );

  useEffect(() => {
    if (nativeApp) {
      setIsOverlayMode(true);
      return;
    }

    function handleResize() {
      setIsOverlayMode(shouldUseOverlaySidebar(window.innerWidth));
    }

    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
    };
  }, [nativeApp]);

  const lastPathnameRef = useRef(location.pathname);

  useEffect(() => {
    if (!nativeApp) {
      return;
    }

    if (lastPathnameRef.current === location.pathname) {
      return;
    }

    lastPathnameRef.current = location.pathname;
    menuPresentation.closeSidebarVisible();
  }, [location.pathname, menuPresentation.closeSidebarVisible, nativeApp]);

  const layoutClassNames = [
    'shellLayout',
    nativeApp ? 'shellLayoutNative' : '',
    !isOverlayMode && !menuPresentation.sidebarVisible ? 'shellLayoutSidebarCollapsed' : '',
    isOverlayMode ? 'shellLayoutSidebarOverlay' : '',
    isOverlayMode && menuPresentation.sidebarVisible ? 'shellLayoutSidebarOpen' : '',
  ]
    .filter(Boolean)
    .join(' ');

  const sidebarCollapsed = !menuPresentation.sidebarVisible;
  const effectiveOpenInNewTab = nativeApp ? false : preferences.openInNewTab;
  const effectiveMenuDisplayMode = nativeApp ? 'operationalOnly' : menuPresentation.menuDisplayMode;

  return (
    <div className={layoutClassNames}>
      <ShellHeader
        sessionContext={sessionContext}
        menuPresentation={menuPresentation}
        openInNewTab={effectiveOpenInNewTab}
        isSavingOpenInNewTab={isSavingOpenInNewTab}
        onOpenInNewTabChange={(openInNewTab) => {
          void updateOpenInNewTab(openInNewTab);
        }}
        onLogout={logout}
        showMobileConfig={nativeApp}
      />
      {isOverlayMode && menuPresentation.sidebarVisible ? (
        <button
          type="button"
          className="shellSidebarBackdrop"
          aria-label={t('shell.menu.closeBackdrop')}
          data-testid="shellSidebarBackdrop"
          onClick={menuPresentation.closeSidebarVisible}
        />
      ) : null}
      <ShellSidebar
        menuItems={filteredMenuItems}
        isLoading={isLoading}
        errorKey={errorKey}
        isCollapsed={sidebarCollapsed}
        menuTreeExpanded={menuPresentation.menuTreeExpanded}
        menuDisplayMode={effectiveMenuDisplayMode}
        openInNewTab={effectiveOpenInNewTab}
        onAfterItemNavigate={nativeApp ? menuPresentation.closeSidebarVisible : undefined}
      />
      <main className="shellMain" data-testid="shellMain">
        <MobileRouteGuard />
        <Outlet />
      </main>
      <ShellFooter userLabel={sessionContext.user.displayName} />
    </div>
  );
}
