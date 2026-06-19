import { useMemo } from 'react';
import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
import { useTranslation } from 'react-i18next';
import type { PivotFieldLayoutState } from '../../../features/pivotLayouts/model/pivotLayoutTypes';
import type { TFunction } from 'i18next';
import type { PivotMetadataResult } from '../../types/pivotMetadata';
import { applyPivotBaseToFields } from '../utils/applyPivotBaseToFields';
import {
  mapMetadataToPivotFields,
  mapTipoDatoToDx,
  type PivotGridFieldConfig,
} from '../utils/mapMetadataToPivotFields';
import { resolvePivotDateFormat } from '../utils/resolvePivotDateFormat';
import { applyPivotNumberFieldFormat } from '../utils/resolvePivotDecimalFormat';
import { preparePivotFields } from '../utils/preparePivotFields';
import { reconcilePivotDataFieldSummaryType } from '../utils/resolvePivotAggregations';
import { resolvePivotCampoForField } from '../utils/resolvePivotCampoForField';
import { resolveConsultaColumnCaption } from '../utils/resolveConsultaColumnCaption';

type UsePivotDataSourceParams = {
  metadata: PivotMetadataResult | null;
  store: Record<string, unknown>[];
  fieldLayout: PivotFieldLayoutState;
  consultaId: string;
  localeKey: string;
};

function reconcileFieldDataTypes(
  fields: PivotGridFieldConfig[],
  metadata: PivotMetadataResult,
  localeKey: string,
): void {
  fields.forEach((field) => {
    const campo = resolvePivotCampoForField(metadata.campos, field.dataField, {
      caption: field.caption,
      dataType: field.dataType,
    });
    const nextDataType = mapTipoDatoToDx(campo.tipoDato);

    if (field.dataType !== nextDataType) {
      field.dataType = nextDataType;
    }

    if (nextDataType === 'date' && !field.format) {
      field.format = resolvePivotDateFormat(localeKey);
    }

    applyPivotNumberFieldFormat(field, campo.tipoDato);

    reconcilePivotDataFieldSummaryType(field, campo);
  });
}

function resolveFields(
  metadata: PivotMetadataResult,
  fieldLayout: PivotFieldLayoutState,
  translate?: TFunction,
): PivotGridFieldConfig[] {
  if (fieldLayout.mode === 'saved' && fieldLayout.configuracionJson?.fields) {
    const savedFields = preparePivotFields(fieldLayout.configuracionJson.fields.map((field) => ({ ...field })));

    if (translate) {
      savedFields.forEach((field) => {
        if (field.dataField) {
          field.caption = resolveConsultaColumnCaption(translate, field.dataField, field.caption);
        }
      });
    }

    return savedFields;
  }

  const baseFields = mapMetadataToPivotFields(metadata.campos, translate);

  if (fieldLayout.mode === 'pivotBase') {
    return preparePivotFields(applyPivotBaseToFields(baseFields, metadata.pivotBase, metadata.campos));
  }

  return preparePivotFields(baseFields);
}

export function usePivotDataSource({
  metadata,
  store,
  fieldLayout,
  consultaId,
  localeKey,
}: UsePivotDataSourceParams): PivotGridDataSource | null {
  const { t } = useTranslation();

  return useMemo(() => {
    if (!metadata) {
      return null;
    }

    const fields = resolveFields(metadata, fieldLayout, t);

    return new PivotGridDataSource({
      fields,
      store,
      onFieldsPrepared: (preparedFields) => {
        reconcileFieldDataTypes(preparedFields as PivotGridFieldConfig[], metadata, localeKey);
      },
    });
  }, [consultaId, fieldLayout, localeKey, metadata, store, t]);
}
