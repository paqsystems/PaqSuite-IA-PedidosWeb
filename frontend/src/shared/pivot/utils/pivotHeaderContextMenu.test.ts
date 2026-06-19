import { describe, expect, it, vi } from 'vitest';
import type { ContextMenuPreparingEvent } from 'devextreme/ui/pivot_grid';
import type PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
import type { PivotMetadataResult } from '../../types/pivotMetadata';
import { buildPivotHeaderContextMenuItems } from './pivotHeaderContextMenu';

const translate = (key: string) => key;

function buildRowEvent(): ContextMenuPreparingEvent {
  return {
    area: 'row',
    cell: { path: ['A'], expanded: undefined },
    rowFields: [{ dataField: 'unidadMedida', name: 'unidadMedida' }],
    columnFields: [],
  } as unknown as ContextMenuPreparingEvent;
}

function buildColumnEvent(): ContextMenuPreparingEvent {
  return {
    area: 'column',
    cell: { path: ['KG'], expanded: undefined },
    rowFields: [],
    columnFields: [{ dataField: 'unidadMedida', name: 'unidadMedida' }],
  } as unknown as ContextMenuPreparingEvent;
}

describe('buildPivotHeaderContextMenuItems', () => {
  const metadata = { campos: [] } as unknown as PivotMetadataResult;

  it('incluye expandir y contraer todo en encabezados de fila con layout standard', () => {
    const dataSource = {
      expandAll: vi.fn(),
      collapseAll: vi.fn(),
      reload: vi.fn().mockResolvedValue(undefined),
      fields: vi.fn().mockReturnValue([]),
    } as unknown as PivotGridDataSource;

    const items = buildPivotHeaderContextMenuItems(
      buildRowEvent(),
      dataSource,
      metadata,
      translate,
      'standard',
    );

    expect(items.map((item) => item.text)).toEqual(['pivot.dx.expandAll', 'pivot.dx.collapseAll']);

    items[0]?.onItemClick();
    items[1]?.onItemClick();

    expect(dataSource.expandAll).toHaveBeenCalledWith('unidadMedida');
    expect(dataSource.collapseAll).toHaveBeenCalledWith('unidadMedida');
  });

  it('mantiene expandir y contraer todo en encabezados de columna con layout standard', () => {
    const dataSource = {
      expandAll: vi.fn(),
      collapseAll: vi.fn(),
      reload: vi.fn().mockResolvedValue(undefined),
    } as unknown as PivotGridDataSource;

    const items = buildPivotHeaderContextMenuItems(
      buildColumnEvent(),
      dataSource,
      metadata,
      translate,
      'standard',
    );

    expect(items.map((item) => item.text)).toEqual(['pivot.dx.expandAll', 'pivot.dx.collapseAll']);
  });
});
