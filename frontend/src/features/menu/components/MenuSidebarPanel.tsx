import { MenuSidebarTree } from '../components/MenuSidebarTree';
import type { MenuNode } from '../menuApi';
import type { MenuDisplayMode } from '../utils/menuPresentationStorage';

type MenuSidebarPanelProps = {
  menuItems: MenuNode[];
  isLoading: boolean;
  errorKey: string | null;
  menuTreeExpanded: boolean;
  menuDisplayMode: MenuDisplayMode;
};

export function MenuSidebarPanel({
  menuItems,
  isLoading,
  errorKey,
  menuTreeExpanded,
  menuDisplayMode,
}: MenuSidebarPanelProps) {
  if (isLoading) {
    return <p data-testid="menuSidebarLoading">Cargando menu...</p>;
  }

  if (errorKey !== null) {
    return (
      <p data-testid="menuSidebarErrorState">
        No se pudo cargar el menu. El portal sigue disponible.
      </p>
    );
  }

  if (menuItems.length === 0) {
    return (
      <p data-testid="menuSidebarEmptyState">
        Sin opciones de menu disponibles para su perfil.
      </p>
    );
  }

  return (
    <nav aria-label="Menu principal">
      <MenuSidebarTree
        menuItems={menuItems}
        menuTreeExpanded={menuTreeExpanded}
        menuDisplayMode={menuDisplayMode}
      />
    </nav>
  );
}
