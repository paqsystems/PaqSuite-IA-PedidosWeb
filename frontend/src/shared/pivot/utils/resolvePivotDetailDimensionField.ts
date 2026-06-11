import type PivotGridDataSource from 'devextreme/ui/pivot_grid/data_source';
import type { PivotCampoMetadata } from '../../types/pivotMetadata';

/** Detalle semántico por dimensión padre (no usar orden del catálogo). */
export const pivotDetailFieldByParent: Record<string, string> = {
  codCliente: 'razonSocial',
  codTransp: 'nomTransp',
  codArticulo: 'descripcion',
};

function rowDataFieldSet(dataSource: PivotGridDataSource): Set<string> {
  return new Set(
    dataSource
      .fields()
      .filter((field) => field.area === 'row' && field.dataField)
      .map((field) => String(field.dataField)),
  );
}

/**
 * Solo devuelve el detalle emparejado (ej. codCliente → razonSocial).
 * Nunca infiere el siguiente campo del catálogo (evita agregar fechas al expandir).
 */
export function resolvePivotDetailDimensionField(
  campos: PivotCampoMetadata[],
  dataSource: PivotGridDataSource,
  currentDataField: string,
): PivotCampoMetadata | undefined {
  const rowDataFields = rowDataFieldSet(dataSource);
  const preferredDataField = pivotDetailFieldByParent[currentDataField];

  if (!preferredDataField || rowDataFields.has(preferredDataField)) {
    return undefined;
  }

  const preferredCampo = campos.find((campo) => campo.dataField === preferredDataField);

  if (preferredCampo && preferredCampo.rolCampo === 'dimension') {
    return preferredCampo;
  }

  return undefined;
}

export function includePivotFieldInRowArea(dataSource: PivotGridDataSource, dataField: string): void {
  const fields = dataSource.fields();
  const fieldIndex = fields.findIndex((field) => field.dataField === dataField);

  if (fieldIndex < 0) {
    return;
  }

  const rowFields = fields.filter((field) => field.area === 'row');
  const nextRowIndex = rowFields.length;

  dataSource.field(fieldIndex, {
    area: 'row',
    areaIndex: nextRowIndex,
  });
}
