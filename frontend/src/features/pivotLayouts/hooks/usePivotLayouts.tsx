import { useCallback, useEffect, useMemo, useState, type RefObject } from 'react';
import type { PivotMetadataResult } from '../../../shared/types/pivotMetadata';
import type { PivotGridBlockHandle } from '../../../shared/pivot/types/pivotGridBlockHandle';
import {
  createPivotLayout,
  deletePivotLayout,
  fetchActivePivotLayout,
  fetchPivotLayouts,
  fetchPublicConfig,
  isPivotLayoutDuplicateError,
  setActivePivotLayout,
  updatePivotLayout,
} from '../api/pivotLayoutsApi';
import { PivotLayoutSaveAsDialog } from '../components/PivotLayoutSaveAsDialog';
import { PivotLayoutToolbar } from '../components/PivotLayoutToolbar';
import type {
  PivotFieldLayoutState,
  PivotLayoutActive,
  PivotLayoutListItem,
} from '../model/pivotLayoutTypes';

type UsePivotLayoutsParams = {
  consultaId: string;
  metadata: PivotMetadataResult | null;
  pivotGridRef: RefObject<PivotGridBlockHandle | null>;
  versionDefinicion: number;
  onFieldLayoutChange: (layout: PivotFieldLayoutState) => void;
  onLayoutBootstrapReady: () => void;
  onVersionMismatch: () => void;
};

function toFieldLayoutState(active: PivotLayoutActive): PivotFieldLayoutState {
  return {
    mode: active.restoreMode,
    configuracionJson: active.configuracionJson,
    version: Date.now(),
  };
}

export function usePivotLayouts({
  consultaId,
  metadata,
  pivotGridRef,
  versionDefinicion,
  onFieldLayoutChange,
  onLayoutBootstrapReady,
  onVersionMismatch,
}: UsePivotLayoutsParams) {
  const [enabled, setEnabled] = useState(false);
  const [layouts, setLayouts] = useState<PivotLayoutListItem[]>([]);
  const [selectedConfigId, setSelectedConfigId] = useState<number | null>(null);
  const [saveAsOpen, setSaveAsOpen] = useState(false);
  const [saveAsErrorKey, setSaveAsErrorKey] = useState<string | null>(null);

  const selectedLayout = layouts.find((layout) => layout.configId === selectedConfigId) ?? null;
  const isInitialTemplate = selectedConfigId === null;
  const canUpdateSave = !isInitialTemplate && Boolean(selectedLayout?.isOwner);
  const saveEnabled = isInitialTemplate || canUpdateSave;
  const canDelete = canUpdateSave;

  const persistenciaHabilitada =
    metadata?.persistencia &&
    typeof metadata.persistencia === 'object' &&
    (metadata.persistencia as { habilitarDiseños?: boolean }).habilitarDiseños === true;

  const refreshLayouts = useCallback(async () => {
    const list = await fetchPivotLayouts(consultaId);
    setLayouts(list);
  }, [consultaId]);

  const applyActiveLayout = useCallback(async () => {
    const active = await fetchActivePivotLayout(consultaId);
    setSelectedConfigId(active.configId);

    if (
      active.versionDefinicionConsulta !== undefined &&
      active.versionDefinicionConsulta < versionDefinicion &&
      active.restoreMode === 'saved'
    ) {
      onVersionMismatch();
    }

    onFieldLayoutChange(toFieldLayoutState(active));
  }, [consultaId, onFieldLayoutChange, onVersionMismatch, versionDefinicion]);

  useEffect(() => {
    let cancelled = false;

    async function initializeLayouts() {
      if (!metadata || !persistenciaHabilitada) {
        onLayoutBootstrapReady();
        return;
      }

      try {
        const config = await fetchPublicConfig();

        if (!config.pivotsEnabled || !config.pivotLayoutsEnabled || cancelled) {
          onLayoutBootstrapReady();
          return;
        }

        setEnabled(true);
        const list = await fetchPivotLayouts(consultaId);

        if (cancelled) {
          return;
        }

        setLayouts(list);
        await applyActiveLayout();
      } catch {
        // Degradación: el pivot sigue operativo sin layouts persistentes.
      } finally {
        if (!cancelled) {
          onLayoutBootstrapReady();
        }
      }
    }

    void initializeLayouts();

    return () => {
      cancelled = true;
    };
  }, [
    applyActiveLayout,
    consultaId,
    metadata,
    onLayoutBootstrapReady,
    persistenciaHabilitada,
  ]);

  const handleSelectLayout = useCallback(
    async (configId: number | null) => {
      setSelectedConfigId(configId);
      await setActivePivotLayout({ consultaId, configId });
      await applyActiveLayout();
    },
    [applyActiveLayout, consultaId],
  );

  const captureCurrentConfiguration = useCallback(() => {
    return pivotGridRef.current?.captureConfiguration() ?? null;
  }, [pivotGridRef]);

  const handleSave = useCallback(async () => {
    const configuracionJson = captureCurrentConfiguration();

    if (!configuracionJson) {
      return;
    }

    if (isInitialTemplate || selectedConfigId === null) {
      setSaveAsErrorKey(null);
      setSaveAsOpen(true);
      return;
    }

    if (!canUpdateSave) {
      return;
    }

    await updatePivotLayout(selectedConfigId, configuracionJson);
    await refreshLayouts();
  }, [canUpdateSave, captureCurrentConfiguration, isInitialTemplate, refreshLayouts, selectedConfigId]);

  const handleSaveAsOpen = useCallback(() => {
    setSaveAsErrorKey(null);
    setSaveAsOpen(true);
  }, []);

  const handleSaveAsConfirm = useCallback(
    async (layoutName: string) => {
      const configuracionJson = captureCurrentConfiguration();

      if (!configuracionJson || layoutName.trim().length === 0) {
        return;
      }

      try {
        const created = await createPivotLayout({
          consultaId,
          nombre: layoutName.trim(),
          configuracionJson,
        });

        setSaveAsOpen(false);
        setSaveAsErrorKey(null);
        await refreshLayouts();
        setSelectedConfigId(created.configId);
        onFieldLayoutChange(toFieldLayoutState(created));
      } catch (error) {
        if (isPivotLayoutDuplicateError(error)) {
          setSaveAsErrorKey('pivotLayout.duplicateName');
          return;
        }

        setSaveAsErrorKey('pivotLayout.error.generic');
      }
    },
    [captureCurrentConfiguration, consultaId, onFieldLayoutChange, refreshLayouts],
  );

  const handleDelete = useCallback(async () => {
    if (!canDelete || selectedConfigId === null) {
      return;
    }

    await deletePivotLayout(selectedConfigId);
    await setActivePivotLayout({ consultaId, configId: null });
    setSelectedConfigId(null);
    await refreshLayouts();
    await applyActiveLayout();
  }, [applyActiveLayout, canDelete, consultaId, refreshLayouts, selectedConfigId]);

  const toolbar = useMemo(() => {
    if (!enabled) {
      return null;
    }

    return (
      <PivotLayoutToolbar
        layouts={layouts}
        selectedConfigId={selectedConfigId}
        saveEnabled={saveEnabled}
        canDelete={canDelete}
        onSelectLayout={(configId) => {
          void handleSelectLayout(configId);
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
    handleDelete,
    handleSave,
    handleSaveAsOpen,
    handleSelectLayout,
    layouts,
    saveEnabled,
    selectedConfigId,
  ]);

  const saveAsDialog = (
    <PivotLayoutSaveAsDialog
      isOpen={saveAsOpen}
      errorKey={saveAsErrorKey}
      onClose={() => setSaveAsOpen(false)}
      onConfirm={(layoutName) => {
        void handleSaveAsConfirm(layoutName);
      }}
    />
  );

  const selectedLayoutNombre = isInitialTemplate
    ? null
    : selectedLayout?.nombre ?? null;

  return {
    enabled,
    toolbar,
    saveAsDialog,
    selectedLayoutNombre,
  };
}
