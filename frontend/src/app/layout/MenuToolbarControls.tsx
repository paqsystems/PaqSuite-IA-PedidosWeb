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
  return (
    <div className="shellMenuControls" aria-label="Controles de menu">
      <button
        type="button"
        className="shellIconButton"
        data-testid="menuToggleSidebar"
        aria-label="Mostrar u ocultar menu lateral"
        onClick={onToggleSidebar}
      >
        ☰
      </button>
      <button
        type="button"
        className="shellIconButton"
        data-testid="menuToggleExpandAll"
        aria-label="Expandir o contraer arbol de menu"
        aria-pressed={menuTreeExpanded}
        title={menuTreeExpanded ? 'Contraer todo' : 'Expandir todo'}
        onClick={onToggleExpandAll}
      >
        ⇅
      </button>
      <button
        type="button"
        className="shellIconButton"
        data-testid="menuToggleDisplayMode"
        aria-label="Cambiar vista operativa del menu"
        aria-pressed={menuDisplayMode === 'operationalOnly'}
        title={
          menuDisplayMode === 'operationalOnly'
            ? 'Vista: solo operativos'
            : 'Vista: todas las ramas'
        }
        onClick={onToggleDisplayMode}
      >
        ◫
      </button>
    </div>
  );
}
