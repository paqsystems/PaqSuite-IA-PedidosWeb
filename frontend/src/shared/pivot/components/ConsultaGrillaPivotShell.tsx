import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Toast from 'devextreme-react/toast';
import { fetchPublicConfig } from '../../../features/config/api/publicConfigApi';
import { PivotExportButton } from '../../../features/pivotExport/components/PivotExportButton';
import { usePivotLayouts } from '../../../features/pivotLayouts/hooks/usePivotLayouts';
import type { PivotFieldLayoutState } from '../../../features/pivotLayouts/model/pivotLayoutTypes';
import { useRequiredSessionContext } from '../../../features/auth/AuthProvider';
import type { PivotMetadataResult } from '../../types/pivotMetadata';
import { fetchPivotDataset, fetchPivotMetadata } from '../services/pivotApi';
import {
  canCoexistGridAndPivot,
  shouldShowPivotOnly,
} from '../utils/resolvePivotCoexistence';
import type { PivotGridBlockHandle } from '../types/pivotGridBlockHandle';
import { PivotGridBlock } from './PivotGridBlock';
import { PivotRefreshButton } from './PivotRefreshButton';
import { PivotViewToggle } from './PivotViewToggle';
import type { PivotViewMode } from '../types/pivotViewMode';

const defaultFieldLayout: PivotFieldLayoutState = {
  mode: 'pivotBase',
  configuracionJson: null,
  version: 0,
};

type ConsultaGrillaPivotShellProps = {
  consultaId: string;
  tipoProceso?: string | null;
  testIdPrefix?: string;
  refreshToken?: number;
  onRefresh?: () => void;
  gridContent: React.ReactNode;
  onDrillDownFilters?: (filters: Record<string, unknown>) => void;
};

export function ConsultaGrillaPivotShell({
  consultaId,
  tipoProceso = null,
  testIdPrefix = 'consultaPivot',
  refreshToken = 0,
  onRefresh,
  gridContent,
  onDrillDownFilters,
}: ConsultaGrillaPivotShellProps) {
  const { t } = useTranslation();
  const session = useRequiredSessionContext();
  const pivotGridRef = useRef<PivotGridBlockHandle>(null);
  const [pivotsEnabled, setPivotsEnabled] = useState(false);
  const [metadata, setMetadata] = useState<PivotMetadataResult | null>(null);
  const [pivotStore, setPivotStore] = useState<Record<string, unknown>[]>([]);
  const [viewMode, setViewMode] = useState<PivotViewMode>('grid');
  const [isPivotLoading, setIsPivotLoading] = useState(false);
  const [pivotLoadError, setPivotLoadError] = useState<string | null>(null);
  const [hasOpenedPivot, setHasOpenedPivot] = useState(false);
  const [fieldLayout, setFieldLayout] = useState<PivotFieldLayoutState>(defaultFieldLayout);
  const [layoutBootstrapReady, setLayoutBootstrapReady] = useState(false);
  const [versionMismatchVisible, setVersionMismatchVisible] = useState(false);
  const [pivotTableLimitedVisible, setPivotTableLimitedVisible] = useState(false);
  const [datasetTruncado, setDatasetTruncado] = useState(false);
  const [configLoaded, setConfigLoaded] = useState(false);

  const handleLayoutBootstrapReady = useCallback(() => {
    setLayoutBootstrapReady(true);
  }, []);

  const handleFieldLayoutChange = useCallback((layout: PivotFieldLayoutState) => {
    setFieldLayout(layout);
  }, []);

  const handleVersionMismatch = useCallback(() => {
    setVersionMismatchVisible(true);
  }, []);

  const { toolbar: pivotLayoutToolbar, saveAsDialog, selectedLayoutNombre } = usePivotLayouts({
    consultaId,
    metadata,
    pivotGridRef,
    versionDefinicion: metadata?.versionDefinicion ?? 1,
    onFieldLayoutChange: handleFieldLayoutChange,
    onLayoutBootstrapReady: handleLayoutBootstrapReady,
    onVersionMismatch: handleVersionMismatch,
  });

  useEffect(() => {
    let cancelled = false;

    const loadConfig = async () => {
      try {
        const config = await fetchPublicConfig();

        if (!cancelled) {
          setPivotsEnabled(config.pivotsEnabled);
        }
      } catch {
        if (!cancelled) {
          setPivotsEnabled(false);
        }
      } finally {
        if (!cancelled) {
          setConfigLoaded(true);
        }
      }
    };

    void loadConfig();

    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!pivotsEnabled) {
      return;
    }

    let cancelled = false;
    setLayoutBootstrapReady(false);
    setFieldLayout(defaultFieldLayout);

    const loadMetadata = async () => {
      try {
        const result = await fetchPivotMetadata(consultaId);

        if (!cancelled) {
          setMetadata(result);
        }
      } catch {
        if (!cancelled) {
          setMetadata(null);
        }
      }
    };

    void loadMetadata();

    return () => {
      cancelled = true;
    };
  }, [consultaId, pivotsEnabled, refreshToken]);

  const mostrarGrillaYPivot = metadata?.configuracionGeneral?.mostrarGrillaYPivot === true;
  const coexistence = metadata
    ? canCoexistGridAndPivot({
        pivotsEnabled,
        pivotHabilitado: metadata.pivotHabilitado,
        tipoProceso,
        mostrarGrillaYPivot,
      })
    : false;
  const pivotOnly = metadata
    ? shouldShowPivotOnly({
        pivotsEnabled,
        pivotHabilitado: metadata.pivotHabilitado,
        tipoProceso,
        mostrarGrillaYPivot,
      })
    : false;

  const buildDatasetFilters = useCallback((): Record<string, unknown> => {
    const filtros: Record<string, unknown> = {};

    metadata?.filtrosGenerales.forEach((filtro) => {
      if (!filtro.obligatorio) {
        return;
      }

      if (filtro.dataField === 'codCliente' && session.codCliente) {
        filtros.codCliente = session.codCliente;
      }
    });

    return filtros;
  }, [metadata?.filtrosGenerales, session.codCliente]);

  const loadPivotDataset = useCallback(async () => {
    if (!metadata) {
      return;
    }

    setIsPivotLoading(true);
    setPivotLoadError(null);

    try {
      const result = await fetchPivotDataset(consultaId, {
        filtros: buildDatasetFilters(),
        pagina: 1,
        tamanoPagina: 500,
      });
      setPivotStore(result.items);
      setDatasetTruncado(result.truncado);
    } catch {
      setPivotStore([]);
      setDatasetTruncado(false);
      setPivotLoadError(t('pivot.error.load'));
    } finally {
      setIsPivotLoading(false);
    }
  }, [buildDatasetFilters, consultaId, metadata, t]);

  useEffect(() => {
    if (!metadata || (!coexistence && !pivotOnly)) {
      return;
    }

    if (pivotOnly) {
      setViewMode('pivot');
    }
  }, [coexistence, metadata, pivotOnly]);

  useEffect(() => {
    const shouldLoadPivotData = viewMode === 'pivot' && metadata !== null && layoutBootstrapReady;

    if (!shouldLoadPivotData) {
      return;
    }

    if (!hasOpenedPivot) {
      setHasOpenedPivot(true);
    }

    void loadPivotDataset();
  }, [hasOpenedPivot, layoutBootstrapReady, loadPivotDataset, metadata, refreshToken, viewMode]);

  const handlePivotRefresh = useCallback(() => {
    onRefresh?.();
    void loadPivotDataset();
  }, [loadPivotDataset, onRefresh]);

  const handleDrillDown = useCallback(
    (filters: Record<string, unknown>) => {
      setViewMode('grid');
      onDrillDownFilters?.(filters);
    },
    [onDrillDownFilters],
  );

  const appliedFilters = useMemo(() => buildDatasetFilters(), [buildDatasetFilters]);

  const pivotExportEmpty = useMemo(() => {
    if (isPivotLoading || pivotStore.length === 0) {
      return true;
    }

    if (fieldLayout.mode === 'empty') {
      return true;
    }

    if (fieldLayout.mode === 'saved') {
      const fields = fieldLayout.configuracionJson?.fields ?? [];

      return !fields.some((field) => field.area === 'data');
    }

    const pivotBase = metadata?.pivotBase as { valores?: unknown[] } | undefined;

    return !(pivotBase?.valores?.length);
  }, [fieldLayout, isPivotLoading, metadata?.pivotBase, pivotStore.length]);

  const pivotExportSlot = useMemo(() => {
    if (!metadata) {
      return null;
    }

    return (
      <PivotExportButton
        pivotGridRef={pivotGridRef}
        consultaId={consultaId}
        metadata={metadata}
        isEmpty={pivotExportEmpty}
        userDisplayName={session.user.displayName}
        activeLayoutName={selectedLayoutNombre}
        appliedFilters={appliedFilters}
        datasetTruncado={datasetTruncado}
        onPivotTableLimited={() => setPivotTableLimitedVisible(true)}
      />
    );
  }, [
    appliedFilters,
    consultaId,
    datasetTruncado,
    metadata,
    pivotExportEmpty,
    selectedLayoutNombre,
    session.user.displayName,
  ]);

  const pivotToolbar = useMemo(
    () => (
      <>
        <PivotRefreshButton onRefresh={handlePivotRefresh} />
        {pivotLayoutToolbar}
        {pivotExportSlot}
      </>
    ),
    [handlePivotRefresh, pivotExportSlot, pivotLayoutToolbar],
  );

  const toggle = useMemo(() => {
    if (!coexistence) {
      return null;
    }

    return (
      <PivotViewToggle
        value={viewMode}
        onChange={(mode) => {
          setViewMode(mode);
        }}
        testIdPrefix={testIdPrefix}
      />
    );
  }, [coexistence, testIdPrefix, viewMode]);

  if (!configLoaded || !pivotsEnabled || !metadata?.pivotHabilitado) {
    return <>{gridContent}</>;
  }

  if (!coexistence && !pivotOnly) {
    return <>{gridContent}</>;
  }

  const showGrid = coexistence ? viewMode === 'grid' : false;
  const showPivot = pivotOnly || viewMode === 'pivot';

  return (
    <div data-testid={`${testIdPrefix}.shell`}>
      {toggle ? <div className="consulta-grilla-pivot-shell__toggle">{toggle}</div> : null}
      {showGrid ? <div data-testid={`${testIdPrefix}.gridView`}>{gridContent}</div> : null}
      {showPivot && layoutBootstrapReady && metadata ? (
        <PivotGridBlock
          ref={pivotGridRef}
          consultaId={consultaId}
          metadata={metadata}
          store={pivotStore}
          fieldLayout={fieldLayout}
          isLoading={isPivotLoading}
          loadError={pivotLoadError}
          testIdPrefix={testIdPrefix}
          toolbarEnd={pivotToolbar}
          onDrillDown={metadata.admiteDrilldown ? handleDrillDown : undefined}
        />
      ) : null}
      {saveAsDialog}
      <Toast
        visible={versionMismatchVisible}
        message={t('pivotLayout.versionMismatch')}
        type="warning"
        displayTime={4000}
        onHiding={() => setVersionMismatchVisible(false)}
      />
      <Toast
        visible={pivotTableLimitedVisible}
        message={t('pivotExport.pivotTableLimited')}
        type="warning"
        displayTime={5000}
        onHiding={() => setPivotTableLimitedVisible(false)}
      />
    </div>
  );
}
