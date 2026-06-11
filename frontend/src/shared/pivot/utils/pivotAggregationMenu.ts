import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import {
  resolvePivotAllowedAggregations,
  type PivotSummaryType,
} from './resolvePivotAggregations';

export type PivotAggregationMenuItem = {
  text: string;
  onItemClick: () => void;
};

const summaryTypeOrder: PivotSummaryType[] = ['sum', 'avg', 'min', 'max', 'count'];

export function buildAggregationMenuItems(params: {
  campo: PivotCampoMetadata;
  translate: (key: string, options?: Record<string, string>) => string;
  onSelect: (summaryType: PivotSummaryType) => void;
}): PivotAggregationMenuItem[] {
  const allowed = new Set(resolvePivotAllowedAggregations(params.campo));

  return summaryTypeOrder
    .filter((summaryType) => allowed.has(summaryType))
    .map((summaryType) => ({
      text: params.translate(`pivot.aggregation.${summaryType}`, { field: params.campo.caption }),
      onItemClick: () => params.onSelect(summaryType),
    }));
}

export { findCampoMetadataByDataField } from './mapMetadataToPivotFields';
