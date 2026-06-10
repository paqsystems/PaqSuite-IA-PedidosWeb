import type dxDataGrid from 'devextreme/ui/data_grid';
import type { CustomSummaryInfo } from 'devextreme/ui/data_grid';

export const PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME = 'paqSummaryFooterPlaceholder';
export const PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY = 'paqSummaryTotalItems';

export type TotalSummaryItem = {
  name?: string;
  column?: string;
  summaryType?: string;
  displayFormat?: string;
};

export function isSummaryFooterPlaceholder(item: TotalSummaryItem): boolean {
  return item.name === PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME;
}

export function filterRealSummaryItems(items: TotalSummaryItem[]): TotalSummaryItem[] {
  return items.filter((item) => !isSummaryFooterPlaceholder(item));
}

export function handleSummaryFooterPlaceholderCalculate(options: CustomSummaryInfo): void {
  if (options.name !== PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME) {
    return;
  }

  if (options.summaryProcess === 'finalize') {
    options.totalValue = '';
  }
}

function resolveFirstDataColumnField(grid: dxDataGrid): string | undefined {
  const column = grid
    .getVisibleColumns()
    .find((item) => item.dataField && item.type !== 'buttons');

  return column?.dataField;
}

/** Garantiza fila de pie visible (DevExtreme oculta el footer si `totalItems` está vacío). */
export function ensureDataGridSummaryFooter(grid: dxDataGrid): void {
  const items = (grid.option('summary.totalItems') as TotalSummaryItem[] | undefined) ?? [];

  if (filterRealSummaryItems(items).length > 0) {
    return;
  }

  if (items.some(isSummaryFooterPlaceholder)) {
    return;
  }

  const firstDataField = resolveFirstDataColumnField(grid);
  if (!firstDataField) {
    return;
  }

  grid.option('summary.totalItems', [
    {
      name: PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME,
      column: firstDataField,
      summaryType: 'custom',
      displayFormat: '',
    },
  ]);
}
