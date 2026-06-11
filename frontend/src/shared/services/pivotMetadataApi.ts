import { apiRequest } from '../http/client';
import type {
  PivotDatasetRequest,
  PivotDatasetResult,
  PivotMetadataResult,
  PivotStructureRequest,
} from '../types/pivotMetadata';

export async function fetchPivotMetadata(consultaId: string): Promise<PivotMetadataResult> {
  const response = await apiRequest<PivotMetadataResult>(
    `/pivots/consultas/${encodeURIComponent(consultaId)}/metadata`,
  );

  return response.resultado;
}

export async function fetchPivotDataset(
  consultaId: string,
  request: PivotDatasetRequest = {},
): Promise<PivotDatasetResult> {
  const response = await apiRequest<PivotDatasetResult>(
    `/pivots/consultas/${encodeURIComponent(consultaId)}/data`,
    {
      method: 'POST',
      body: JSON.stringify(request),
    },
  );

  return response.resultado;
}

export async function validatePivotStructure(
  consultaId: string,
  structure: PivotStructureRequest,
): Promise<{ valido: boolean }> {
  const response = await apiRequest<{ valido: boolean }>(
    `/pivots/consultas/${encodeURIComponent(consultaId)}/validate-structure`,
    {
      method: 'POST',
      body: JSON.stringify(structure),
    },
  );

  return response.resultado;
}
