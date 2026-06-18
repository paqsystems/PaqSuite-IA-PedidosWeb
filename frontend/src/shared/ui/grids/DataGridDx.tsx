/**
 * Wrapper transversal DevExtreme DataGrid (SPEC-001-03 / TR-GEN-03-grillas-listados).
 * @see docs/05-open-spec/001-Generaliddes/SPEC-001-03-ui-transversal.md
 * @see docs/00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md — i18n filtros, menús DX, pie por columna
 */
import { forwardRef, useCallback, useEffect, useImperativeHandle, useMemo, useRef, useState } from 'react';
import { GridExportButton } from '../gridExport';
import { useTranslation } from 'react-i18next';
import type {
  CellPreparedEvent,
  ContentReadyEvent,
  ContextMenuPreparingEvent,
  InitNewRowEvent,
  RowInsertingEvent,
  ToolbarPreparingEvent,
} from 'devextreme/ui/data_grid';
import DataGrid, {
  Button,
  Column,
  ColumnChooser,
  Editing,
  FilterRow,
  GroupPanel,
  LoadPanel,
  Pager,
  Paging,
  Sorting,
  Summary,
  Toolbar,
  Item,
  type DataGridRef,
} from 'devextreme-react/data-grid';
import { useDataGridDevExtremeTexts } from './hooks/useDataGridDevExtremeTexts';
import { prepareDataGridSummaryContextMenu } from './utils/dataGridSummaryContextMenu';
import {
  ensureDataGridSummaryFooter,
  filterRealSummaryItems,
  handleSummaryFooterPlaceholderCalculate,
  PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY,
  type TotalSummaryItem,
} from './utils/dataGridSummaryFooter';
import { abmTestIds } from '../abm/abmTestIds';
import { buildAbmRowActions, resolveAbmRowActionTestId } from './utils/buildAbmRowActions';
import {
  getDataGridDxContainerTestId,
  getDataGridDxToolbarTestId,
  getDataGridRowActionTestId,
} from './dataGridDxTestIds';
import { useDataGridDxState } from './hooks/useDataGridDxState';
import type { DataGridDxHandle } from './types/dataGridDxHandle';
import type { DataGridDxProps, DataGridRowAction } from './types/dataGridDxTypes';
import './dataGridDx.css';

function resolveRowActionFlag<TRecord>(
  value: boolean | ((row: TRecord) => boolean) | undefined,
  row: TRecord,
  defaultValue: boolean,
): boolean {
  if (value === undefined) {
    return defaultValue;
  }

  return typeof value === 'function' ? value(row) : value;
}

function RowActionsColumn<TRecord extends Record<string, unknown>>({
  rowActions,
}: {
  rowActions: DataGridRowAction<TRecord>[];
}) {
  const { t } = useTranslation();

  return (
    <Column type="buttons" width={Math.max(44, rowActions.length * 40)} caption={t('grid.column.actions')}>
      {rowActions.map((action) => (
        <Button
          key={action.actionKey}
          icon={action.icon}
          hint={t(action.hintKey)}
          visible={(cell) =>
            resolveRowActionFlag(action.visible, (cell.row?.data ?? {}) as TRecord, true)
          }
          disabled={(cell) =>
            resolveRowActionFlag(action.disabled, (cell.row?.data ?? {}) as TRecord, false)
          }
          onClick={(cell) => {
            const row = cell.row?.data as TRecord | undefined;
            if (row) {
              action.onClick?.(row);
            }
          }}
        />
      ))}
    </Column>
  );
}

function DataGridDxInner<TRecord extends Record<string, unknown> = Record<string, unknown>>(
  {
    proceso: _proceso,
    gridId,
    dataSource,
    keyExpr = 'id',
    children,
    rowActions,
    toolbarStart,
    toolbarEnd,
    isLoading = false,
    loadError = null,
    emptyMessageKey = 'grid.empty',
    sortingMode = 'single',
    enableGrouping = true,
    enableSummary = true,
    defaultPageSize = 10,
    abm,
    exportEnabled = true,
    onRowPrepared,
  }: DataGridDxProps<TRecord>,
  ref: React.ForwardedRef<DataGridDxHandle>,
) {
  const { t } = useTranslation();
  const dxTexts = useDataGridDevExtremeTexts();
  const dataGridRef = useRef<DataGridRef>(null);
  const viewState = useDataGridDxState({ isLoading, loadError });
  const [visibleRowCount, setVisibleRowCount] = useState(0);
  const exportActive = exportEnabled !== false;
  const showToolbar = Boolean(toolbarStart || toolbarEnd || exportActive);
  const abmActive = Boolean(abm?.enabled);
  const isGridEmpty = viewState === 'ready' && visibleRowCount === 0;

  const updateVisibleRowCount = useCallback(() => {
    const instance = dataGridRef.current?.instance();

    if (!instance) {
      setVisibleRowCount(0);
      return;
    }

    const dataRows = instance.getVisibleRows().filter((row) => row.rowType === 'data');
    setVisibleRowCount(dataRows.length);
  }, []);

  const handleContentReady = useCallback(
    (event: ContentReadyEvent) => {
      updateVisibleRowCount();

      if (enableSummary) {
        ensureDataGridSummaryFooter(event.component);
      }
    },
    [enableSummary, updateVisibleRowCount],
  );

  useEffect(() => {
    updateVisibleRowCount();
  }, [dataSource, updateVisibleRowCount, viewState]);

  const effectiveRowActions = useMemo(() => {
    const abmActions = abmActive && abm ? buildAbmRowActions(abm) : [];
    const customActions = rowActions ?? [];

    return [...abmActions, ...customActions];
  }, [abm, abmActive, rowActions]);

  const noDataText = useMemo(() => t(emptyMessageKey), [emptyMessageKey, t]);

  const gridDisabled = viewState === 'error';

  useImperativeHandle(
    ref,
    () => ({
      captureState: () => {
        const instance = dataGridRef.current?.instance();

        if (!instance) {
          return null;
        }

        const baseState = instance.state() as Record<string, unknown>;
        const totalItems = filterRealSummaryItems(
          (instance.option('summary.totalItems') as TotalSummaryItem[] | undefined) ?? [],
        );

        if (totalItems.length === 0) {
          return baseState;
        }

        return {
          ...baseState,
          [PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY]: totalItems,
        };
      },
      applyState: (state) => {
        const instance = dataGridRef.current?.instance();

        if (!instance) {
          return;
        }

        if (!state) {
          instance.state(null);

          if (enableSummary) {
            ensureDataGridSummaryFooter(instance);
          }

          updateVisibleRowCount();
          return;
        }

        const stateRecord = state as Record<string, unknown>;
        const summaryTotalItems = stateRecord[PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY];
        const gridState = { ...stateRecord };
        delete gridState[PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY];

        instance.state(gridState);

        if (Array.isArray(summaryTotalItems) && summaryTotalItems.length > 0) {
          instance.option('summary.totalItems', summaryTotalItems);
        } else if (enableSummary) {
          ensureDataGridSummaryFooter(instance);
        }

        updateVisibleRowCount();
      },
      getVisibleRowCount: () => visibleRowCount,
    }),
    [enableSummary, updateVisibleRowCount, visibleRowCount],
  );

  const handleCellPrepared = (event: CellPreparedEvent) => {
    if (event.rowType === 'totalFooter' && event.cellElement) {
      event.cellElement.classList.add('dataGridDx__summaryCell');
      return;
    }

    if (event.rowType !== 'data' || event.column?.type !== 'buttons' || !effectiveRowActions.length) {
      return;
    }

    const links = event.cellElement?.querySelectorAll<HTMLElement>('.dx-link');
    effectiveRowActions.forEach((action, index) => {
      const testId = abmActive
        ? resolveAbmRowActionTestId(action.actionKey)
        : getDataGridRowActionTestId(action.actionKey);
      links?.[index]?.setAttribute('data-testid', testId);
    });
  };

  const handleInitNewRow = (event: InitNewRowEvent) => {
    if (!abmActive || !abm?.permissions.alta) {
      return;
    }

    abm.onCreate();
    window.setTimeout(() => {
      event.component.cancelEditData();
    }, 0);
  };

  const handleRowInserting = (event: RowInsertingEvent) => {
    if (abmActive) {
      event.cancel = true;
    }
  };

  const handleContextMenuPreparing = (event: ContextMenuPreparingEvent) => {
    if (enableSummary) {
      prepareDataGridSummaryContextMenu(event, t);
    }
  };

  const handleToolbarPreparing = (event: ToolbarPreparingEvent) => {
    if (!abmActive || !abm?.permissions.alta) {
      return;
    }

    const items = event.toolbarOptions.items ?? [];

    for (const item of items) {
      if (item && typeof item === 'object' && 'name' in item && item.name === 'addRowButton') {
        item.options = {
          ...(item.options ?? {}),
          elementAttr: {
            ...((item.options?.elementAttr as Record<string, string> | undefined) ?? {}),
            'data-testid': abmTestIds.addRow,
          },
        };
      }
    }
  };

  return (
    <div
      className="dataGridDx"
      data-testid={getDataGridDxContainerTestId(gridId)}
      data-proceso={_proceso}
      data-grid-id={gridId}
    >
      {showToolbar ? (
        <div className="dataGridDx__toolbar" data-testid={getDataGridDxToolbarTestId(gridId)}>
          <div className="dataGridDx__toolbarStart">{toolbarStart}</div>
          <div className="dataGridDx__toolbarEnd">
            {toolbarEnd}
            {exportActive ? (
              <GridExportButton
                gridRef={dataGridRef}
                proceso={_proceso}
                gridId={gridId}
                exportEnabled={exportActive}
                isEmpty={isGridEmpty}
              />
            ) : null}
          </div>
        </div>
      ) : null}

      {viewState === 'error' ? (
        <p className="dataGridDx__message dataGridDx__message--error" data-testid="dataGridDxError">
          {loadError ?? t('grid.error.load')}
        </p>
      ) : null}

      {viewState === 'loading' ? (
        <p className="dataGridDx__message dataGridDx__message--loading" data-testid="dataGridDxLoading">
          {t('grid.loading')}
        </p>
      ) : null}

      <DataGrid
        key={`${gridId}-${dxTexts.localeKey}`}
        ref={dataGridRef}
        dataSource={dataSource}
        keyExpr={keyExpr}
        showBorders={true}
        showColumnLines={true}
        showRowLines={true}
        allowColumnReordering={true}
        allowColumnResizing={true}
        columnAutoWidth={true}
        noDataText={noDataText}
        disabled={gridDisabled}
        onCellPrepared={handleCellPrepared}
        onRowPrepared={onRowPrepared}
        onInitNewRow={handleInitNewRow}
        onRowInserting={handleRowInserting}
        onToolbarPreparing={handleToolbarPreparing}
        onContextMenuPreparing={handleContextMenuPreparing}
        onContentReady={handleContentReady}
        onOptionChanged={(event) => {
          if (
            event.fullName === 'paging.pageIndex' ||
            event.fullName?.startsWith('columns') ||
            event.fullName?.startsWith('filterValue')
          ) {
            updateVisibleRowCount();
          }
        }}
      >
        {abmActive ? (
          <>
            <Editing mode="row" allowAdding={Boolean(abm?.permissions.alta)} allowUpdating={false} allowDeleting={false} />
            <Toolbar>
              <Item name="addRowButton" />
            </Toolbar>
          </>
        ) : null}
        <LoadPanel enabled={viewState === 'loading'} />
        <FilterRow visible={true} operationDescriptions={dxTexts.filterOperationDescriptions} />
        <Sorting mode={sortingMode} />
        <Paging defaultPageSize={defaultPageSize} />
        <Pager visible={true} showPageSizeSelector={true} displayMode="full" />
        <ColumnChooser
          enabled={true}
          mode="select"
          title={dxTexts.columnChooserTitle}
          emptyPanelText={dxTexts.columnChooserEmptyText}
        />
        {enableGrouping ? (
          <GroupPanel visible={true} emptyPanelText={dxTexts.groupPanelEmptyText} />
        ) : null}
        {enableSummary ? (
          <Summary
            recalculateWhileEditing={true}
            calculateCustomSummary={handleSummaryFooterPlaceholderCalculate}
          />
        ) : null}
        {children}
        {effectiveRowActions.length > 0 ? (
          <RowActionsColumn rowActions={effectiveRowActions} />
        ) : null}
      </DataGrid>
    </div>
  );
}

export const DataGridDx = forwardRef(DataGridDxInner) as <
  TRecord extends Record<string, unknown> = Record<string, unknown>,
>(
  props: DataGridDxProps<TRecord> & { ref?: React.ForwardedRef<DataGridDxHandle> },
) => ReturnType<typeof DataGridDxInner>;
