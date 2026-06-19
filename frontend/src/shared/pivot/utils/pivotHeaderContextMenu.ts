import type { TFunction } from 'i18next';
import type PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
import type { ContextMenuPreparingEvent } from 'devextreme/ui/pivot_grid';
import type { PivotMetadataResult } from '../../types/pivotMetadata';
import {
  includePivotFieldInRowArea,
  resolvePivotDetailDimensionField,
} from './resolvePivotDetailDimensionField';
import { resolveConsultaColumnCaption } from './resolveConsultaColumnCaption';

type PivotHeaderContextMenuItem = {
  text: string;
  onItemClick: () => void;
};

function reloadDataSource(dataSource: PivotGridDataSource): void {
  void dataSource.reload();
}

function resolveHeaderField(
  event: ContextMenuPreparingEvent,
  area: 'row' | 'column',
): { dataField?: string; fieldKey?: number | string } {
  const pathLength = event.cell?.path?.length ?? 0;
  const fields = area === 'row' ? event.rowFields : event.columnFields;
  const field = fields?.[Math.max(0, pathLength - 1)];

  if (!field) {
    return {};
  }

  return {
    dataField: field.dataField,
    fieldKey: field.dataField ?? field.name,
  };
}

export function buildPivotHeaderContextMenuItems(
  event: ContextMenuPreparingEvent,
  dataSource: PivotGridDataSource,
  metadata: PivotMetadataResult,
  translate: TFunction,
  rowHeaderLayout: 'standard' | 'tree' = 'standard',
): PivotHeaderContextMenuItem[] {
  const area = event.area;

  if (area !== 'row' && area !== 'column') {
    return [];
  }

  const path = event.cell?.path;

  if (!path || path.length === 0) {
    return [];
  }

  const items: PivotHeaderContextMenuItem[] = [];
  const { dataField, fieldKey } = resolveHeaderField(event, area);

  if (rowHeaderLayout === 'tree') {
    if (event.cell?.expanded === false) {
      items.push({
        text: translate('pivot.menu.expand'),
        onItemClick: () => {
          dataSource.expandHeaderItem(area, path);
          reloadDataSource(dataSource);
        },
      });
    }

    if (event.cell?.expanded === true) {
      items.push({
        text: translate('pivot.menu.collapse'),
        onItemClick: () => {
          dataSource.collapseHeaderItem(area, path);
          reloadDataSource(dataSource);
        },
      });
    }

    if (fieldKey !== undefined) {
      items.push(
        {
          text: translate('pivot.dx.expandAll'),
          onItemClick: () => {
            dataSource.expandAll(fieldKey);
            reloadDataSource(dataSource);
          },
        },
        {
          text: translate('pivot.dx.collapseAll'),
          onItemClick: () => {
            dataSource.collapseAll(fieldKey);
            reloadDataSource(dataSource);
          },
        },
      );
    }
  }

  if (area === 'row' && dataField) {
    const detailField = resolvePivotDetailDimensionField(metadata.campos, dataSource, dataField);

    if (detailField) {
      items.push({
        text: translate('pivot.menu.includeDetail', {
          field: resolveConsultaColumnCaption(translate, detailField.dataField, detailField.caption),
        }),
        onItemClick: () => {
          includePivotFieldInRowArea(dataSource, detailField.dataField);

          if (rowHeaderLayout === 'tree') {
            dataSource.expandHeaderItem('row', path);
          }

          reloadDataSource(dataSource);
        },
      });
    }
  }

  if (fieldKey !== undefined && rowHeaderLayout === 'standard' && (area === 'column' || area === 'row')) {
    items.push(
      {
        text: translate('pivot.dx.expandAll'),
        onItemClick: () => {
          dataSource.expandAll(fieldKey);
          reloadDataSource(dataSource);
        },
      },
      {
        text: translate('pivot.dx.collapseAll'),
        onItemClick: () => {
          dataSource.collapseAll(fieldKey);
          reloadDataSource(dataSource);
        },
      },
    );
  }

  return items;
}
