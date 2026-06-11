import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import { mapTipoDatoToDx } from './mapMetadataToPivotFields';

/**
 * Fuente de verdad — formato decimal pivot DevExtreme (paridad grillas `#,##0.00`).
 * Aplica a celdas de valores y totalizadores/subtotales del PivotGrid.
 */
export const pivotDecimalDxFormat = '#,##0.00';

export function isPivotNumericTipoDato(tipoDato: string): boolean {
  const normalized = tipoDato.trim().toLowerCase();

  return normalized === 'number' || normalized === 'numeric' || normalized === 'decimal';
}

export function resolvePivotCampoFormatoMetadata(tipoDato: string): PivotCampoMetadata['formato'] {
  if (!isPivotNumericTipoDato(tipoDato)) {
    return null;
  }

  return { format: pivotDecimalDxFormat };
}

/** Formato DevExtreme para PivotGridField (string). */
export function resolvePivotFieldFormat(
  tipoDato: string,
  formato?: PivotCampoMetadata['formato'] | null,
): string | undefined {
  if (isPivotNumericTipoDato(tipoDato)) {
    return pivotDecimalDxFormat;
  }

  if (formato && typeof formato === 'object' && 'format' in formato) {
    const formatValue = formato.format;

    if (typeof formatValue === 'string' && formatValue.trim() !== '') {
      return formatValue;
    }
  }

  return undefined;
}

export function applyPivotNumberFieldFormat(
  field: { dataType?: string; format?: string | Record<string, unknown> },
  tipoDato: string,
): void {
  if (mapTipoDatoToDx(tipoDato) !== 'number') {
    return;
  }

  field.format = pivotDecimalDxFormat;
}
