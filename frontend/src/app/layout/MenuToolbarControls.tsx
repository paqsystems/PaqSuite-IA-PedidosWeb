import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { isNativeApp } from '../../shared/platform/isNativeApp';

type MenuToolbarControlsProps = {
  menuTreeExpanded: boolean;
  menuDisplayMode: 'allBranches' | 'operationalOnly';
  onToggleSidebar: () => void;
  onToggleExpandAll: () => void;
  onToggleDisplayMode: () => void;
  compact?: boolean;
};

export function MenuToolbarControls({
  menuTreeExpanded,
  menuDisplayMode,
  onToggleSidebar,
  onToggleExpandAll,
  onToggleDisplayMode,
  compact = false,
}: MenuToolbarControlsProps) {
  const { t } = useTranslation();
  const nativeApp = isNativeApp();

  return (
    <div className="shellMenuControls" aria-label={t('shell.menu.controls')}>
      {nativeApp ? (
        <Button
          icon="menu"
          stylingMode="text"
          className="shellIconButton shellIconButton--dx"
          elementAttr={{
            'data-testid': 'menuToggleSidebar',
            'aria-label': t('shell.menu.toggleSidebar'),
          }}
          onClick={onToggleSidebar}
        />
      ) : (
        <button
          type="button"
          className="shellIconButton"
          data-testid="menuToggleSidebar"
          aria-label={t('shell.menu.toggleSidebar')}
          onClick={onToggleSidebar}
        >
          ☰
        </button>
      )}
      {!compact && (
        <>
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
        </>
      )}
    </div>
  );
}
