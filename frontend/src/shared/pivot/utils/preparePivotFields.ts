import type { PivotGridFieldConfig } from './mapMetadataToPivotFields';

/**
 * Normaliza campos antes de crear el PivotGridDataSource.
 * - areaIndex coherente en filas (columnas adyacentes en layout standard).
 * - Sin expanded persistido (el modo tree reemplaza el código de cliente por «Total»).
 */
export function preparePivotFields(fields: PivotGridFieldConfig[]): PivotGridFieldConfig[] {
  const rowFieldIndexes = fields
    .map((field, index) => ({ field, index }))
    .filter(({ field }) => field.area === 'row')
    .sort((left, right) => (left.field.areaIndex ?? left.index) - (right.field.areaIndex ?? right.index));

  return fields.map((field, index) => {
    const { expanded: _expanded, ...rest } = field;
    const rowOrder = rowFieldIndexes.findIndex(({ index: fieldIndex }) => fieldIndex === index);

    if (rowOrder >= 0) {
      return {
        ...rest,
        areaIndex: rowOrder,
      };
    }

    return rest;
  });
}
