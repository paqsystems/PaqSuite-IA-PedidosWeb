export type PivotDataFieldRef = {
  dataField?: string;
};

export function resolvePivotDataFieldIndex(
  fields: PivotDataFieldRef[],
  dataField: string | undefined,
): number {
  if (!dataField) {
    return -1;
  }

  return fields.findIndex((field) => field.dataField === dataField);
}
