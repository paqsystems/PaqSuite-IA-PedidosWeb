import type { ContextMenuPreparingEvent } from 'devextreme/ui/data_grid';
import type { TFunction } from 'i18next';
import {
  ensureDataGridSummaryFooter,
  filterRealSummaryItems,
  type TotalSummaryItem,
} from './dataGridSummaryFooter';

export type SummaryDataType = 'numeric' | 'date' | 'string' | 'boolean' | 'other';

export type SummaryTypeOption = 'count' | 'sum' | 'avg' | 'min' | 'max';

export function resolveSummaryDataType(dataType: string | undefined): SummaryDataType {
  if (dataType === 'number') {
    return 'numeric';
  }

  if (dataType === 'date' || dataType === 'datetime') {
    return 'date';
  }

  if (dataType === 'boolean') {
    return 'boolean';
  }

  if (dataType === 'string') {
    return 'string';
  }

  return 'other';
}

export function getSummaryTypesForDataType(dataType: SummaryDataType): SummaryTypeOption[] {
  switch (dataType) {
    case 'numeric':
      return ['sum', 'avg', 'min', 'max', 'count'];
    case 'date':
      return ['min', 'max', 'count'];
    case 'string':
    case 'boolean':
    case 'other':
      return ['count', 'min', 'max'];
    default:
      return ['count'];
  }
}

function getDisplayFormat(summaryType: SummaryTypeOption, t: TFunction): string {
  switch (summaryType) {
    case 'sum':
      return t('grid.dx.summary.sum');
    case 'avg':
      return t('grid.dx.summary.avg');
    case 'min':
      return t('grid.dx.summary.min');
    case 'max':
      return t('grid.dx.summary.max');
    case 'count':
    default:
      return t('grid.dx.summary.count');
  }
}

function getSummaryTypeLabel(summaryType: SummaryTypeOption, t: TFunction): string {
  return t(`grid.summary.type.${summaryType}`);
}

export function prepareDataGridSummaryContextMenu(
  event: ContextMenuPreparingEvent,
  t: TFunction,
): void {
  if (event.target !== 'footer' || !event.column?.dataField) {
    return;
  }

  const columnField = event.column.dataField;
  const dataType = resolveSummaryDataType(event.column.dataType);
  const allowedTypes = getSummaryTypesForDataType(dataType);
  const grid = event.component;
  const currentItems = (grid.option('summary.totalItems') as TotalSummaryItem[] | undefined) ?? [];
  const currentItem = filterRealSummaryItems(currentItems).find((item) => item.column === columnField);

  const typeItems = allowedTypes.map((summaryType) => ({
    text: getSummaryTypeLabel(summaryType, t),
    selected: currentItem?.summaryType === summaryType,
    onItemClick: () => {
      const nextItems = filterRealSummaryItems(
        currentItems.filter((item) => item.column !== columnField),
      );
      nextItems.push({
        column: columnField,
        summaryType,
        displayFormat: getDisplayFormat(summaryType, t),
      });
      grid.option('summary.totalItems', nextItems);
    },
  }));

  event.items = [
    { text: t('grid.summary.menuTitle'), disabled: true },
    ...typeItems,
    {
      text: t('grid.summary.remove'),
      onItemClick: () => {
        const nextItems = filterRealSummaryItems(
          currentItems.filter((item) => item.column !== columnField),
        );
        grid.option('summary.totalItems', nextItems);
        if (nextItems.length === 0) {
          ensureDataGridSummaryFooter(grid);
        }
      },
    },
  ];
}
