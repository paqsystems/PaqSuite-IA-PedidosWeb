import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import { mapAgregacionToSummaryType, mapTipoDatoToDx } from './mapMetadataToPivotFields';

export type PivotSummaryType = 'sum' | 'avg' | 'min' | 'max' | 'count';

const numericAggregations: PivotSummaryType[] = ['sum', 'avg', 'min', 'max', 'count'];
const discreteAggregations: PivotSummaryType[] = ['count', 'min', 'max'];
const summaryTypeOrder: PivotSummaryType[] = ['sum', 'avg', 'min', 'max', 'count'];

function resolvePivotAllowedAggregationsByTipoDato(tipoDato: string): PivotSummaryType[] {
  const dataType = mapTipoDatoToDx(tipoDato);

  if (dataType === 'string' || dataType === 'date') {
    return [...discreteAggregations];
  }

  return [...numericAggregations];
}

function mapMetadataAggregations(
  agregacionesPermitidas: string[] | null | undefined,
): PivotSummaryType[] {
  if (!agregacionesPermitidas?.length) {
    return [];
  }

  return summaryTypeOrder.filter((summaryType) =>
    agregacionesPermitidas.some(
      (item) => mapAgregacionToSummaryType(item) === summaryType,
    ),
  );
}

/** Usa metadata del backend; fallback local por tipo de dato (tests / mocks). */
export function resolvePivotAllowedAggregations(campo: PivotCampoMetadata): PivotSummaryType[] {
  const fromMetadata = mapMetadataAggregations(campo.agregacionesPermitidas);

  if (fromMetadata.length > 0) {
    return fromMetadata;
  }

  return resolvePivotAllowedAggregationsByTipoDato(campo.tipoDato);
}

export function resolvePivotDefaultSummaryType(campo: PivotCampoMetadata): PivotSummaryType {
  const allowed = resolvePivotAllowedAggregations(campo);
  const preferred = mapAgregacionToSummaryType(campo.agregacionDefault);

  if (preferred && allowed.includes(preferred)) {
    return preferred;
  }

  const dataType = mapTipoDatoToDx(campo.tipoDato);

  if (dataType === 'string' || dataType === 'date') {
    return 'count';
  }

  return 'sum';
}

export function reconcilePivotDataFieldSummaryType(
  field: { area?: string; summaryType?: PivotSummaryType },
  campo: PivotCampoMetadata,
): void {
  if (field.area !== 'data') {
    return;
  }

  const allowed = resolvePivotAllowedAggregations(campo);
  const current = field.summaryType;

  if (!current || !allowed.includes(current)) {
    field.summaryType = resolvePivotDefaultSummaryType(campo);
  }
}
