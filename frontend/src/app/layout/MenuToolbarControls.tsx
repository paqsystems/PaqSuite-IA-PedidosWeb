import { useTranslation } from 'react-i18next';

type MenuToolbarControlsProps = {
  menuTreeExpanded: boolean;
  menuDisplayMode: 'allBranches' | 'operationalOnly';
  onToggleSidebar: () => void;
  onToggleExpandAll: () => void;
  onToggleDisplayMode: () => void;
};

export function MenuToolbarControls({
  menuTreeExpanded,
  menuDisplayMode,
  onToggleSidebar,
  onToggleExpandAll,
  onToggleDisplayMode,
}: MenuToolbarControlsProps) {
  const { t } = useTranslation();

  return (
    <div className="shellMenuControls" aria-label={t('shell.menu.controls')}>
      <button
        type="button"
        className="shellIconButton"
        data-testid="menuToggleSidebar"
        aria-label={t('shell.menu.toggleSidebar')}
        onClick={onToggleSidebar}
      >
        ☰
      </button>
      <button
        type="button"
        className="shellIconButton"
        data-testid="menuToggleExpandAll"
        aria-label={t('shell.menu.toggleExpand')}
        aria-pressed={menuTreeExpanded}
        title={menuTreeExpanded ? t('shell.menu.collapseAll') : t('shell.menu.expandAll')}
        onClick={onToggleExpandAll}
      >
        ⇅
      </button>
      <button
        type="button"
        className="shellIconButton"
        data-testid="menuToggleDisplayMode"
        aria-label={t('shell.menu.toggleDisplayMode')}
        aria-pressed={menuDisplayMode === 'operationalOnly'}
        title={
          menuDisplayMode === 'operationalOnly'
            ? t('shell.menu.viewOperational')
            : t('shell.menu.viewAllBranches')
        }
        onClick={onToggleDisplayMode}
      >
        ◫
      </button>
    </div>
  );
}
