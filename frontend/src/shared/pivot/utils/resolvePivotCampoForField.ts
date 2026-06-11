import type { PivotCampoMetadata } from '../../types/pivotMetadata';
import { findCampoMetadataByDataField } from './mapMetadataToPivotFields';
import { resolvePivotCampoFormatoMetadata } from './resolvePivotDecimalFormat';

type PivotFieldHint = {
  caption?: string;
  dataType?: string;
};

export function mapDxDataTypeToTipoDato(dataType?: string): string {
  if (dataType === 'number') {
    return 'number';
  }

  if (dataType === 'date' || dataType === 'datetime') {
    return 'date';
  }

  return 'string';
}

function inferTipoDatoFromStoreValue(value: unknown): string {
  if (typeof value === 'number' && Number.isFinite(value)) {
    return 'number';
  }

  if (value instanceof Date) {
    return 'date';
  }

  return 'string';
}

/** Catálogo metadata o campo sintético (dataset / diseño guardado sin fila en pq_pivots_campos). */
export function resolvePivotCampoForField(
  campos: PivotCampoMetadata[],
  dataField: string,
  dxField?: PivotFieldHint,
): PivotCampoMetadata {
  const fromCatalog = findCampoMetadataByDataField(campos, dataField);

  if (fromCatalog) {
    return fromCatalog;
  }

  const tipoDato = mapDxDataTypeToTipoDato(dxField?.dataType);

  return {
    campoId: dataField,
    dataField,
    caption: dxField?.caption ?? dataField,
    tipoDato,
    rolCampo: tipoDato === 'number' ? 'metrica' : 'dimension',
    rolesPermitidos: ['fila', 'columna', 'valor'],
    agregacionDefault: tipoDato === 'number' ? 'sum' : 'count',
    agregacionesPermitidas: null,
    formato: resolvePivotCampoFormatoMetadata(tipoDato),
  };
}

export function inferPivotCampoFromStoreSample(
  dataField: string,
  sampleValue: unknown,
): PivotCampoMetadata {
  const tipoDato = inferTipoDatoFromStoreValue(sampleValue);

  return resolvePivotCampoForField([], dataField, {
    dataType: mapDxDataTypeToTipoDato(tipoDato === 'number' ? 'number' : tipoDato === 'date' ? 'date' : 'string'),
  });
}
