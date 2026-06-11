import type PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';

export function isPivotExportEmpty(
  store: Record<string, unknown>[],
  dataSource: PivotGridDataSource | null | undefined,
): boolean {
  if (!store.length || !dataSource) {
    return true;
  }

  const fields = dataSource.fields();
  const hasDataField = fields.some((field) => field.area === 'data');

  return !hasDataField;
}
