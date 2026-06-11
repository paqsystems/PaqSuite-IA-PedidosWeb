import { mapAgregacionToSummaryType } from './mapMetadataToPivotFields';

export type PivotAggregationMenuItem = {
  text: string;
  onItemClick: () => void;
};

const summaryTypeOrder = ['sum', 'avg', 'min', 'max', 'count'] as const;

export function buildAggregationMenuItems(params: {
  allowedAgregaciones: string[] | null | undefined;
  caption: string;
  translate: (key: string, options?: Record<string, string>) => string;
  onSelect: (summaryType: 'sum' | 'avg' | 'min' | 'max' | 'count') => void;
}): PivotAggregationMenuItem[] {
  const allowed = new Set(
    (params.allowedAgregaciones ?? summaryTypeOrder).map((item) =>
      mapAgregacionToSummaryType(item),
    ),
  );

  return summaryTypeOrder
    .filter((summaryType) => allowed.has(summaryType))
    .map((summaryType) => ({
      text: params.translate(`pivot.aggregation.${summaryType}`, { field: params.caption }),
      onItemClick: () => params.onSelect(summaryType),
    }));
}

export { findCampoMetadataByDataField } from './mapMetadataToPivotFields';
