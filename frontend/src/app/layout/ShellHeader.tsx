import { useTranslation } from 'react-i18next';
import { MenuToolbarControls } from './MenuToolbarControls';
import { LocaleSelector } from '../../features/i18n/components/LocaleSelector';
import { useCurrentLocale } from '../../features/i18n/hooks/useCurrentLocale';
import { AvatarMenu } from '../../features/avatar/components/AvatarMenu';
import type { SessionContext } from '../../features/auth/types';
import type { useMenuPresentation } from '../../features/menu/hooks/useMenuPresentation';

type MenuPresentationControls = ReturnType<typeof useMenuPresentation>;

type ShellHeaderProps = {
  sessionContext: SessionContext;
  menuPresentation: MenuPresentationControls;
  openInNewTab: boolean;
  isSavingOpenInNewTab: boolean;
  onOpenInNewTabChange: (openInNewTab: boolean) => void;
  onLogout: () => void;
};

export function ShellHeader({
  sessionContext,
  menuPresentation,
  openInNewTab,
  isSavingOpenInNewTab,
  onOpenInNewTabChange,
  onLogout,
}: ShellHeaderProps) {
  const { t } = useTranslation();
  const { currentLocale, changeLocale, isSaving, saveErrorKey } = useCurrentLocale();

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
        <p className="shellHeaderBrand">{t('app.title')}</p>
      </div>

      <div className="shellHeaderEnd">
        <LocaleSelector
          testId="localeSelectorHeader"
          value={currentLocale}
          disabled={isSaving}
          onChange={(locale) => {
            void changeLocale(locale);
          }}
        />
        {saveErrorKey !== null && (
          <span data-testid="locale-save-error">{t(saveErrorKey)}</span>
        )}
        <AvatarMenu
          displayName={sessionContext.user.displayName}
          openInNewTab={openInNewTab}
          isSavingOpenInNewTab={isSavingOpenInNewTab}
          onOpenInNewTabChange={onOpenInNewTabChange}
          onLogout={onLogout}
        />
      </div>
    </header>
  );
}
