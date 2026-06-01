import { useCallback, useEffect, useMemo, useState, type RefObject } from 'react';
import type { DataGridDxHandle } from '../../../shared/ui/grids/types/dataGridDxHandle';
import {
  createGridLayout,
  deleteGridLayout,
  fetchActiveGridLayout,
  fetchGridLayouts,
  fetchPublicConfig,
  isGridLayoutDuplicateError,
  setActiveGridLayout,
  updateGridLayout,
} from '../api/gridLayoutsApi';
import { GridLayoutSaveAsDialog } from '../components/GridLayoutSaveAsDialog';
import { GridLayoutToolbar } from '../components/GridLayoutToolbar';
import type { GridLayoutListItem } from '../model/gridLayoutTypes';

type UseGridLayoutsParams = {
  proceso: string;
  gridId: string;
  gridRef: RefObject<DataGridDxHandle | null>;
};

export function useGridLayouts({ proceso, gridId, gridRef }: UseGridLayoutsParams) {
  const [enabled, setEnabled] = useState(false);
  const [layouts, setLayouts] = useState<GridLayoutListItem[]>([]);
  const [selectedLayoutId, setSelectedLayoutId] = useState<number | null>(null);
  const [saveAsOpen, setSaveAsOpen] = useState(false);
  const [saveAsErrorKey, setSaveAsErrorKey] = useState<string | null>(null);

  const selectedLayout = layouts.find((layout) => layout.id === selectedLayoutId) ?? null;
  const isSystemTemplate = selectedLayoutId === null;
  const canUpdateSave = !isSystemTemplate && Boolean(selectedLayout?.isOwner);
  const saveEnabled = isSystemTemplate || canUpdateSave;
  const canDelete = canUpdateSave;

  const refreshLayouts = useCallback(async () => {
    const list = await fetchGridLayouts(proceso, gridId);
    setLayouts(list);
  }, [gridId, proceso]);

  const applyActiveLayout = useCallback(async () => {
    const active = await fetchActiveGridLayout(proceso, gridId);
    setSelectedLayoutId(active.layoutId);
    gridRef.current?.applyState(active.stateJson);
  }, [gridId, gridRef, proceso]);

  useEffect(() => {
    let cancelled = false;

    async function initializeLayouts() {
      try {
        const config = await fetchPublicConfig();

        if (!config.gridLayoutsEnabled || cancelled) {
          return;
        }

        setEnabled(true);
        const list = await fetchGridLayouts(proceso, gridId);

        if (cancelled) {
          return;
        }

        setLayouts(list);
        await applyActiveLayout();
      } catch {
        // Degradación: la grilla sigue operativa sin layouts persistentes.
      }
    }

    void initializeLayouts();

    return () => {
      cancelled = true;
    };
  }, [applyActiveLayout, gridId, proceso]);

  const handleSelectLayout = useCallback(
    async (layoutId: number | null) => {
      setSelectedLayoutId(layoutId);
      await setActiveGridLayout({ proceso, gridId, layoutId });
      await applyActiveLayout();
    },
    [applyActiveLayout, gridId, proceso],
  );

  const captureCurrentState = useCallback((): Record<string, unknown> | null => {
    return gridRef.current?.captureState() ?? null;
  }, [gridRef]);

  const handleSave = useCallback(async () => {
    const stateJson = captureCurrentState();

    if (!stateJson) {
      return;
    }

    if (isSystemTemplate || selectedLayoutId === null) {
      setSaveAsErrorKey(null);
      setSaveAsOpen(true);
      return;
    }

    if (!canUpdateSave) {
      return;
    }

    await updateGridLayout(selectedLayoutId, stateJson);
    await refreshLayouts();
  }, [canUpdateSave, captureCurrentState, isSystemTemplate, refreshLayouts, selectedLayoutId]);

  const handleSaveAsOpen = useCallback(() => {
    setSaveAsErrorKey(null);
    setSaveAsOpen(true);
  }, []);

  const handleSaveAsConfirm = useCallback(
    async (layoutName: string) => {
      const stateJson = captureCurrentState();

      if (!stateJson || layoutName.trim().length === 0) {
        return;
      }

      try {
        const created = await createGridLayout({
          proceso,
          gridId,
          layoutName: layoutName.trim(),
          stateJson,
        });

        setSaveAsOpen(false);
        setSaveAsErrorKey(null);
        await refreshLayouts();
        setSelectedLayoutId(created.layoutId);
      } catch (error) {
        if (isGridLayoutDuplicateError(error)) {
          setSaveAsErrorKey('gridLayout.duplicateName');
          return;
        }

        setSaveAsErrorKey('gridLayout.error.generic');
      }
    },
    [captureCurrentState, gridId, proceso, refreshLayouts],
  );

  const handleDelete = useCallback(async () => {
    if (!canDelete || selectedLayoutId === null) {
      return;
    }

    await deleteGridLayout(selectedLayoutId);
    await setActiveGridLayout({ proceso, gridId, layoutId: null });
    setSelectedLayoutId(null);
    await refreshLayouts();
    gridRef.current?.applyState(null);
  }, [canDelete, gridId, gridRef, proceso, refreshLayouts, selectedLayoutId]);

  const toolbar = useMemo(() => {
    if (!enabled) {
      return null;
    }

    return (
      <GridLayoutToolbar
        layouts={layouts}
        selectedLayoutId={selectedLayoutId}
        saveEnabled={saveEnabled}
        canDelete={canDelete}
        onSelectLayout={(layoutId) => {
          void handleSelectLayout(layoutId);
        }}
        onSave={() => {
          void handleSave();
        }}
        onSaveAs={handleSaveAsOpen}
        onDelete={() => {
          void handleDelete();
        }}
      />
    );
  }, [
    canDelete,
    enabled,
    saveEnabled,
    handleDelete,
    handleSave,
    handleSaveAsOpen,
    handleSelectLayout,
    layouts,
    selectedLayoutId,
  ]);

  const saveAsDialog = (
    <GridLayoutSaveAsDialog
      isOpen={saveAsOpen}
      errorKey={saveAsErrorKey}
      onClose={() => setSaveAsOpen(false)}
      onConfirm={(layoutName) => {
        void handleSaveAsConfirm(layoutName);
      }}
    />
  );

  return {
    enabled,
    toolbar,
    saveAsDialog,
  };
}
