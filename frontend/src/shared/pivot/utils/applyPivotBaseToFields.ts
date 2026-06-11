import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import {
  mapAgregacionToSummaryType,
  type PivotGridFieldConfig,
} from './mapMetadataToPivotFields';
import { resolvePivotDataFieldIndex } from './resolvePivotDataFieldIndex';
import { resolvePivotDefaultSummaryType } from './resolvePivotAggregations';

type PivotBaseValor = {
  campoId?: string;
  agregacion?: string;
};

export function applyPivotBaseToFields(
  fields: PivotGridFieldConfig[],
  pivotBase: Record<string, unknown>,
  campos: PivotCampoMetadata[],
): PivotGridFieldConfig[] {
  const campoById = Object.fromEntries(campos.map((campo) => [campo.campoId, campo]));
  const nextFields: PivotGridFieldConfig[] = fields.map((field) => ({ ...field }));

  const assignArea = (campoIds: unknown, area: NonNullable<PivotGridFieldConfig['area']>) => {
    if (!Array.isArray(campoIds)) {
      return;
    }

    campoIds.forEach((campoId, areaIndex) => {
      const campo = campoById[String(campoId)];
      const index = resolvePivotDataFieldIndex(nextFields, campo?.dataField);

      if (index >= 0) {
        nextFields[index] = {
          ...nextFields[index],
          area,
          areaIndex,
        };
      }
    });
  };

  assignArea(pivotBase.filas, 'row');
  assignArea(pivotBase.columnas, 'column');
  assignArea(pivotBase.filtrosInternos, 'filter');

  const valores = pivotBase.valores;

  if (Array.isArray(valores)) {
    valores.forEach((valor) => {
      const typedValor = valor as PivotBaseValor;
      const campo = campoById[String(typedValor.campoId ?? '')];
      const index = resolvePivotDataFieldIndex(nextFields, campo?.dataField);

      if (index >= 0) {
        nextFields[index] = {
          ...nextFields[index],
          area: 'data',
          summaryType: mapAgregacionToSummaryType(typedValor.agregacion ?? campo?.agregacionDefault)
            ?? (campo ? resolvePivotDefaultSummaryType(campo) : 'sum'),
        };
      }
    });
  }

  return nextFields;
}
