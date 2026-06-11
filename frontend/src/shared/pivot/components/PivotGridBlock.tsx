import { forwardRef, useCallback, useImperativeHandle, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import PivotGrid, { FieldChooser, FieldPanel, type PivotGridRef } from 'devextreme-react/pivot-grid';
import { isPivotExportEmpty } from '../../../features/pivotExport/utils/isPivotExportEmpty';
import type { CellClickEvent, ContextMenuPreparingEvent } from 'devextreme/ui/pivot_grid';
import type { PivotFieldLayoutState } from '../../../features/pivotLayouts/model/pivotLayoutTypes';
import type { PivotMetadataResult } from '../../types/pivotMetadata';
import type { PivotGridBlockHandle } from '../types/pivotGridBlockHandle';
import { usePivotDataSource } from '../hooks/usePivotDataSource';
import { usePivotDevExtremeTexts } from '../hooks/usePivotDevExtremeTexts';
import {
  buildAggregationMenuItems,
  findCampoMetadataByDataField,
} from '../utils/pivotAggregationMenu';
import type { PivotGridFieldConfig } from '../utils/mapMetadataToPivotFields';
import { resolvePivotDataFieldIndex } from '../utils/resolvePivotDataFieldIndex';

type PivotGridBlockProps = {
  consultaId: string;
  metadata: PivotMetadataResult;
  store: Record<string, unknown>[];
  fieldLayout: PivotFieldLayoutState;
  isLoading?: boolean;
  loadError?: string | null;
  testIdPrefix?: string;
  toolbarEnd?: React.ReactNode;
  onDrillDown?: (filters: Record<string, unknown>) => void;
};

export const PivotGridBlock = forwardRef<PivotGridBlockHandle, PivotGridBlockProps>(function PivotGridBlock(
  {
    consultaId,
    metadata,
    store,
    fieldLayout,
    isLoading = false,
    loadError = null,
    testIdPrefix = 'pivot',
    toolbarEnd,
    onDrillDown,
  },
  ref,
) {
  const { t } = useTranslation();
  const dxTexts = usePivotDevExtremeTexts();
  const pivotGridComponentRef = useRef<PivotGridRef>(null);

  const dataSource = usePivotDataSource({
    metadata,
    store,
    fieldLayout,
    consultaId,
    localeKey: dxTexts.localeKey,
  });

  useImperativeHandle(
    ref,
    () => ({
      captureConfiguration: () => {
        if (!dataSource) {
          return null;
        }

        return {
          fields: dataSource.fields().map((field) => ({ ...field })) as PivotGridFieldConfig[],
        };
      },
      getPivotGridInstance: () => pivotGridComponentRef.current?.instance() ?? null,
      isExportEmpty: () => isPivotExportEmpty(store, dataSource),
    }),
    [dataSource, store],
  );

  const pivotFlags = metadata.pivotBase as {
    mostrarSubtotales?: boolean;
    mostrarTotalesGenerales?: boolean;
  };

  const showTotals = pivotFlags.mostrarTotalesGenerales !== false;
  const showSubtotals = pivotFlags.mostrarSubtotales !== false;

  const appendAggregationMenu = useCallback(
    (event: ContextMenuPreparingEvent) => {
      const field = event.field;

      if (!field || field.area !== 'data' || !dataSource) {
        return;
      }

      const campo = findCampoMetadataByDataField(metadata.campos, field.dataField);

      if (!campo) {
        return;
      }

      const menuItems = buildAggregationMenuItems({
        allowedAgregaciones: campo.agregacionesPermitidas,
        caption: campo.caption,
        translate: t,
        onSelect: (summaryType) => {
          const fields = dataSource.fields();
          const index = resolvePivotDataFieldIndex(fields, field.dataField);

          if (index >= 0) {
            dataSource.field(index, { summaryType });
          }
        },
      });

      event.items = [...(event.items ?? []), ...menuItems];
    },
    [dataSource, metadata.campos, t],
  );

  const handleContextMenuPreparing = useCallback(
    (event: ContextMenuPreparingEvent) => {
      appendAggregationMenu(event);
    },
    [appendAggregationMenu],
  );

  const handleCellClick = useCallback(
    (event: CellClickEvent) => {
      if (!metadata.admiteDrilldown || event.area !== 'data' || !onDrillDown) {
        return;
      }

      const filters: Record<string, unknown> = {};

      event.rowFields?.forEach((field, index) => {
        if (field.dataField) {
          filters[field.dataField] = event.cell?.rowPath?.[index];
        }
      });

      event.columnFields?.forEach((field, index) => {
        if (field.dataField) {
          filters[field.dataField] = event.cell?.columnPath?.[index];
        }
      });

      if (Object.keys(filters).length > 0) {
        onDrillDown(filters);
      }
    },
    [metadata.admiteDrilldown, onDrillDown],
  );

  if (loadError) {
    return <p role="alert">{loadError}</p>;
  }

  if (!dataSource) {
    return <p>{t('pivot.loading')}</p>;
  }

  return (
    <div className="pivot-grid-block" data-testid={`${testIdPrefix}.pivotRoot`}>
      {toolbarEnd ? <div className="pivot-grid-block__toolbar">{toolbarEnd}</div> : null}
      <PivotGrid
        ref={pivotGridComponentRef}
        key={`${consultaId}-${dxTexts.localeKey}-${fieldLayout.version}`}
        dataSource={dataSource}
        allowSortingBySummary={true}
        allowFiltering={true}
        showBorders={true}
        showColumnGrandTotals={showTotals}
        showColumnTotals={showSubtotals}
        showRowGrandTotals={showTotals}
        showRowTotals={showSubtotals}
        onContextMenuPreparing={handleContextMenuPreparing}
        onCellClick={handleCellClick}
        disabled={isLoading}
        elementAttr={{ 'data-testid': `${testIdPrefix}.pivotGrid` }}
      >
        <FieldPanel visible={true} allowFieldDragging={true} />
        <FieldChooser enabled={true} allowSearch={true} applyChangesMode="instantly" />
      </PivotGrid>
    </div>
  );
});
