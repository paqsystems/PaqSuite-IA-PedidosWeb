import type { TFunction } from 'i18next';
import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import {
  resolvePivotAllowedAggregations,
  type PivotSummaryType,
} from './resolvePivotAggregations';
import { resolveConsultaColumnCaption } from './resolveConsultaColumnCaption';

export type PivotAggregationMenuItem = {
  text: string;
  onItemClick: () => void;
};

const summaryTypeOrder: PivotSummaryType[] = ['sum', 'avg', 'min', 'max', 'count'];

export function buildAggregationMenuItems(params: {
  campo: PivotCampoMetadata;
  translate: TFunction;
  onSelect: (summaryType: PivotSummaryType) => void;
}): PivotAggregationMenuItem[] {
  const allowed = new Set(resolvePivotAllowedAggregations(params.campo));

  return summaryTypeOrder
    .filter((summaryType) => allowed.has(summaryType))
    .map((summaryType) => ({
      text: params.translate(`pivot.aggregation.${summaryType}`, {
        field: resolveConsultaColumnCaption(params.translate, params.campo.dataField, params.campo.caption),
      }),
      onItemClick: () => params.onSelect(summaryType),
    }));
}

export { findCampoMetadataByDataField } from './mapMetadataToPivotFields';
