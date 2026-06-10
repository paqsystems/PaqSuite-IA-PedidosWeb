import { useTranslation } from 'react-i18next';
import { MenuSidebarTree } from '../components/MenuSidebarTree';
import type { MenuNode } from '../menuApi';
import type { MenuDisplayMode } from '../utils/menuPresentationStorage';

type MenuSidebarPanelProps = {
  menuItems: MenuNode[];
  isLoading: boolean;
  errorKey: string | null;
  menuTreeExpanded: boolean;
  menuDisplayMode: MenuDisplayMode;
  openInNewTab: boolean;
};

export function MenuSidebarPanel({
  menuItems,
  isLoading,
  errorKey,
  menuTreeExpanded,
  menuDisplayMode,
  openInNewTab,
}: MenuSidebarPanelProps) {
  const { t } = useTranslation();

  if (isLoading) {
    return <p data-testid="menuSidebarLoading">{t('shell.menu.loading')}</p>;
  }

  if (errorKey !== null) {
    return (
      <p data-testid="menuSidebarErrorState">
        {t('shell.menu.error')}
      </p>
    );
  }

  if (menuItems.length === 0) {
    return (
      <p data-testid="menuSidebarEmptyState">
        {t('shell.menu.empty')}
      </p>
    );
  }

  return (
    <nav aria-label="Menu principal">
      <MenuSidebarTree
        menuItems={menuItems}
        menuTreeExpanded={menuTreeExpanded}
        menuDisplayMode={menuDisplayMode}
        openInNewTab={openInNewTab}
      />
    </nav>
  );
}
