import { Link } from 'react-router-dom';
import { MenuToolbarControls } from './MenuToolbarControls';
import type { SessionContext } from '../../features/auth/types';
import type { useMenuPresentation } from '../../features/menu/hooks/useMenuPresentation';
import type { ResolvedUserPreferences } from '../../features/preferences/userPreferences';

type MenuPresentationControls = ReturnType<typeof useMenuPresentation>;

type ShellHeaderProps = {
  sessionContext: SessionContext;
  preferences: ResolvedUserPreferences;
  menuPresentation: MenuPresentationControls;
  onLogout: () => void;
};

export function ShellHeader({
  sessionContext,
  preferences,
  menuPresentation,
  onLogout,
}: ShellHeaderProps) {
  return (
    <header className="shellHeader" data-testid="shellHeader">
      <div className="shellHeaderStart">
        <MenuToolbarControls
          menuTreeExpanded={menuPresentation.menuTreeExpanded}
          menuDisplayMode={menuPresentation.menuDisplayMode}
          onToggleSidebar={menuPresentation.toggleSidebarVisible}
          onToggleExpandAll={menuPresentation.toggleMenuTreeExpanded}
          onToggleDisplayMode={menuPresentation.toggleMenuDisplayMode}
        />
        <p className="shellHeaderBrand">PedidosWeb</p>
      </div>

      <div className="shellHeaderEnd">
        <span className="shellHeaderSlot" data-testid="shell-language-slot">
          Idioma: {preferences.locale}
        </span>
        <Link to="/change-password" data-testid="avatar-change-password">
          Cambiar contraseña
        </Link>
        <button type="button" data-testid="avatar-logout" onClick={onLogout}>
          {sessionContext.user.displayName}
        </button>
      </div>
    </header>
  );
}
