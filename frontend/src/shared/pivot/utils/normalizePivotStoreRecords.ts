import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import { mapTipoDatoToDx } from './mapMetadataToPivotFields';

export function normalizePivotStoreRecords(
  store: Record<string, unknown>[],
  campos: PivotCampoMetadata[],
): Record<string, unknown>[] {
  const dateFields = campos
    .filter((campo) => mapTipoDatoToDx(campo.tipoDato) === 'date')
    .map((campo) => campo.dataField);

  if (dateFields.length === 0) {
    return store;
  }

  return store.map((row) => {
    const nextRow = { ...row };

    dateFields.forEach((field) => {
      const value = nextRow[field];

      if (value instanceof Date) {
        return;
      }

      if (typeof value === 'string' && value.trim() !== '') {
        const parsed = new Date(value);

        if (!Number.isNaN(parsed.getTime())) {
          nextRow[field] = parsed;
        }
      }
    });

    return nextRow;
  });
}
