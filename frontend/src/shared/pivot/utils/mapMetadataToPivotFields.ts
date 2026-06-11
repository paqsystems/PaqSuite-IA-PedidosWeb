import type { PivotCampoMetadata } from '../../types/pivotMetadata';

export function findCampoMetadataByDataField(
  campos: PivotCampoMetadata[],
  dataField: string | undefined,
): PivotCampoMetadata | undefined {
  return campos.find((campo) => campo.dataField === dataField);
}

export type PivotGridFieldConfig = {
  caption: string;
  dataField: string;
  dataType?: 'string' | 'number' | 'date';
  area?: 'row' | 'column' | 'data' | 'filter';
  summaryType?: 'sum' | 'avg' | 'min' | 'max' | 'count';
  format?: string | Record<string, unknown>;
  expanded?: boolean;
};

export function mapTipoDatoToDx(tipoDato: string): 'string' | 'number' | 'date' {
  const normalized = tipoDato.trim().toLowerCase();

  if (normalized === 'number' || normalized === 'numeric' || normalized === 'decimal') {
    return 'number';
  }

  if (normalized === 'date' || normalized === 'datetime') {
    return 'date';
  }

  return 'string';
}

export function mapAgregacionToSummaryType(
  agregacion: string | undefined | null,
): PivotGridFieldConfig['summaryType'] {
  const normalized = (agregacion ?? 'sum').trim().toLowerCase();

  if (normalized === 'avg' || normalized === 'promedio' || normalized === 'average') {
    return 'avg';
  }

  if (normalized === 'count' || normalized === 'contar') {
    return 'count';
  }

  if (normalized === 'min' || normalized === 'minimo') {
    return 'min';
  }

  if (normalized === 'max' || normalized === 'maximo') {
    return 'max';
  }

  return 'sum';
}

export function mapMetadataToPivotFields(campos: PivotCampoMetadata[]): PivotGridFieldConfig[] {
  return campos.map((campo) => ({
    caption: campo.caption,
    dataField: campo.dataField,
    dataType: mapTipoDatoToDx(campo.tipoDato),
    summaryType:
      campo.rolCampo === 'metrica'
        ? mapAgregacionToSummaryType(campo.agregacionDefault)
        : undefined,
    format:
      campo.formato && typeof campo.formato === 'object' && 'format' in campo.formato
        ? (campo.formato.format as string | Record<string, unknown>)
        : undefined,
  }));
}
