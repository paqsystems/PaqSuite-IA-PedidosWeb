import { useMemo } from 'react';
import PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
import type { PivotFieldLayoutState } from '../../../features/pivotLayouts/model/pivotLayoutTypes';
import type { PivotMetadataResult } from '../../types/pivotMetadata';
import { applyPivotBaseToFields } from '../utils/applyPivotBaseToFields';
import {
  findCampoMetadataByDataField,
  mapMetadataToPivotFields,
  mapTipoDatoToDx,
  type PivotGridFieldConfig,
} from '../utils/mapMetadataToPivotFields';

type UsePivotDataSourceParams = {
  metadata: PivotMetadataResult | null;
  store: Record<string, unknown>[];
  fieldLayout: PivotFieldLayoutState;
  consultaId: string;
  localeKey: string;
};

function reconcileFieldDataTypes(fields: PivotGridFieldConfig[], metadata: PivotMetadataResult): void {
  fields.forEach((field) => {
    const campo = findCampoMetadataByDataField(metadata.campos, field.dataField);
    const nextDataType = mapTipoDatoToDx(campo?.tipoDato ?? 'string');

    if (field.dataType !== nextDataType) {
      field.dataType = nextDataType;
    }
  });
}

function resolveFields(
  metadata: PivotMetadataResult,
  fieldLayout: PivotFieldLayoutState,
): PivotGridFieldConfig[] {
  if (fieldLayout.mode === 'saved' && fieldLayout.configuracionJson?.fields) {
    return fieldLayout.configuracionJson.fields.map((field) => ({ ...field }));
  }

  const baseFields = mapMetadataToPivotFields(metadata.campos);

  if (fieldLayout.mode === 'pivotBase') {
    return applyPivotBaseToFields(baseFields, metadata.pivotBase, metadata.campos);
  }

  return baseFields;
}

export function usePivotDataSource({
  metadata,
  store,
  fieldLayout,
  consultaId,
  localeKey,
}: UsePivotDataSourceParams): PivotGridDataSource | null {
  return useMemo(() => {
    if (!metadata) {
      return null;
    }

    const fields = resolveFields(metadata, fieldLayout);

    return new PivotGridDataSource({
      fields,
      store,
      onFieldsPrepared: (preparedFields) => {
        reconcileFieldDataTypes(preparedFields as PivotGridFieldConfig[], metadata);
      },
    });
  }, [consultaId, fieldLayout, localeKey, metadata, store]);
}
