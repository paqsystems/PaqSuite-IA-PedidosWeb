import { buildTenantHeaders, getApiBaseUrl } from '../../../shared/http/client';
import type { ExcelImportHostResult } from '../types/excelImportHostTypes';

export type ExcelImportProcesoMeta = {
  codigoProceso: string;
  nombreProceso: string;
  generaPlantilla: boolean;
  permiteProcesamientoParcial: boolean;
  permiteSoloValidar: boolean;
  procedimientoHost: string;
};

export type ExcelImportLotSummary = {
  guidImportacion: string;
  estadoImportacion: string;
  esAsincronica: boolean;
  cantidadFilasLeidas: number;
  cantidadFilasDescartadas: number;
  cantidadFilasValidas: number;
  cantidadFilasConError: number;
  cantidadFilasProcesadas?: number;
  codigoProceso?: string;
  nombreProceso?: string;
  archivoOriginalNombre?: string;
  hojaSeleccionada?: string;
  permiteProcesamientoParcial?: boolean;
  permiteSoloValidar?: boolean;
  puedeCancelar?: boolean;
  mensajeResultado?: string | null;
};

export type ExcelStagingColumn = {
  dataField: string;
  caption: string;
  tipoDato: string;
  format?: string | null;
  fixed?: boolean;
};

export type ExcelStagingColumnasMeta = {
  columnas: ExcelStagingColumn[];
  permiteProcesamientoParcial: boolean;
  permiteSoloValidar: boolean;
  puedeProcesar: boolean;
  cantidadFilasValidas: number;
  cantidadFilasConError: number;
  estadoImportacion: string;
};

export type ExcelStagingFila = {
  idImportacionFila: number;
  numeroFilaExcel: number;
  tieneError: boolean;
  errorImportacion: string | null;
  estadoFila: string;
  datos: Record<string, unknown>;
};

export type ExcelHistorialRow = {
  guidImportacion: string;
  codigoProceso: string;
  nombreProceso: string;
  usuarioEjecucion: string;
  archivoOriginalNombre: string;
  hojaSeleccionada: string;
  estadoImportacion: string;
  fechaInicio: string;
  fechaFin: string | null;
  cantidadFilasLeidas: number;
  cantidadFilasValidas: number;
  cantidadFilasConError: number;
  cantidadFilasProcesadas: number;
  cantidadFilasDescartadas: number;
};

type Envelope<T> = {
  error: number;
  respuesta: string;
  resultado: T;
};

async function authHeaders(contentType?: string): Promise<Headers> {
  const headers = new Headers(buildTenantHeaders());
  const token = localStorage.getItem('pedidosweb.auth.token');
  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }
  if (contentType) {
    headers.set('Content-Type', contentType);
  }

  return headers;
}

async function parseEnvelope<T>(response: Response): Promise<Envelope<T>> {
  const payload = (await response.json()) as Envelope<T>;
  if (!response.ok) {
    throw new Error(payload.respuesta ?? 'request.failed');
  }

  return payload;
}

export async function fetchExcelImportProceso(codigoProceso: string): Promise<ExcelImportProcesoMeta> {
  const response = await fetch(`${getApiBaseUrl()}/excel-import/procesos/${encodeURIComponent(codigoProceso)}`, {
    headers: await authHeaders('application/json'),
  });
  const payload = await parseEnvelope<ExcelImportProcesoMeta>(response);

  return payload.resultado;
}

export async function downloadExcelTemplate(codigoProceso: string): Promise<Blob> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/procesos/${encodeURIComponent(codigoProceso)}/plantilla`,
    { headers: await authHeaders() },
  );

  if (!response.ok) {
    const payload = (await response.json()) as Envelope<unknown>;
    throw new Error(payload.respuesta ?? 'excelImport.plantillaNoDisponible');
  }

  return response.blob();
}

export async function listExcelSheets(codigoProceso: string, archivo: File): Promise<string[]> {
  const formData = new FormData();
  formData.append('archivo', archivo);

  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/procesos/${encodeURIComponent(codigoProceso)}/archivo/hojas`,
    {
      method: 'POST',
      headers: await authHeaders(),
      body: formData,
    },
  );
  const payload = await parseEnvelope<{ hojas: string[] }>(response);

  return payload.resultado.hojas ?? [];
}

export async function createExcelImportLot(
  codigoProceso: string,
  archivo: File,
  hojaSeleccionada: string,
): Promise<ExcelImportLotSummary> {
  const formData = new FormData();
  formData.append('archivo', archivo);
  formData.append('hojaSeleccionada', hojaSeleccionada);

  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/procesos/${encodeURIComponent(codigoProceso)}/lotes`,
    {
      method: 'POST',
      headers: await authHeaders(),
      body: formData,
    },
  );
  const payload = await parseEnvelope<ExcelImportLotSummary>(response);

  return payload.resultado;
}

export async function fetchExcelImportLot(guidImportacion: string): Promise<ExcelImportLotSummary> {
  const response = await fetch(`${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}`, {
    headers: await authHeaders('application/json'),
  });
  const payload = await parseEnvelope<ExcelImportLotSummary>(response);

  return payload.resultado;
}

export async function cancelExcelImportLot(guidImportacion: string): Promise<void> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/cancelar`,
    {
      method: 'POST',
      headers: await authHeaders('application/json'),
    },
  );
  await parseEnvelope(response);
}

export async function fetchExcelStagingColumnas(guidImportacion: string): Promise<ExcelStagingColumnasMeta> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/columnas`,
    { headers: await authHeaders('application/json') },
  );
  const payload = await parseEnvelope<ExcelStagingColumnasMeta>(response);

  return payload.resultado;
}

export async function fetchExcelStagingFilas(
  guidImportacion: string,
  page: number,
  pageSize: number,
  soloConError?: boolean,
): Promise<{ items: ExcelStagingFila[]; total: number; page: number; pageSize: number }> {
  const params = new URLSearchParams({
    page: String(page),
    pageSize: String(pageSize),
  });
  if (soloConError === true) {
    params.set('soloConError', 'true');
  }
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/filas?${params}`,
    { headers: await authHeaders('application/json') },
  );
  const payload = await parseEnvelope<{
    items: ExcelStagingFila[];
    total: number;
    page: number;
    pageSize: number;
  }>(response);

  return payload.resultado;
}

export async function processExcelImportLot(guidImportacion: string): Promise<{
  estadoImportacion: string;
  cantidadFilasProcesadas: number;
  cantidadFilasOmitidas: number;
}> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/procesar`,
    {
      method: 'POST',
      headers: await authHeaders('application/json'),
    },
  );
  const payload = await parseEnvelope<{
    estadoImportacion: string;
    cantidadFilasProcesadas: number;
    cantidadFilasOmitidas: number;
  }>(response);

  return payload.resultado;
}

export async function fetchExcelImportHistorial(params: {
  page: number;
  pageSize: number;
  codigoProceso?: string;
  estadoImportacion?: string;
  usuarioEjecucion?: string;
  fechaDesde?: string;
  fechaHasta?: string;
}): Promise<{ items: ExcelHistorialRow[]; total: number }> {
  const search = new URLSearchParams({
    page: String(params.page),
    pageSize: String(params.pageSize),
  });
  if (params.codigoProceso) search.set('codigoProceso', params.codigoProceso);
  if (params.estadoImportacion) search.set('estadoImportacion', params.estadoImportacion);
  if (params.usuarioEjecucion) search.set('usuarioEjecucion', params.usuarioEjecucion);
  if (params.fechaDesde) search.set('fechaDesde', params.fechaDesde);
  if (params.fechaHasta) search.set('fechaHasta', params.fechaHasta);

  const response = await fetch(`${getApiBaseUrl()}/excel-import/historial?${search}`, {
    headers: await authHeaders('application/json'),
  });
  const payload = await parseEnvelope<{ items: ExcelHistorialRow[]; total: number }>(response);

  return payload.resultado;
}

export function flattenStagingRow(fila: ExcelStagingFila): Record<string, unknown> {
  return {
    idImportacionFila: fila.idImportacionFila,
    numeroFilaExcel: fila.numeroFilaExcel,
    tieneError: fila.tieneError,
    errorImportacion: fila.errorImportacion ?? '',
    estadoFila: fila.estadoFila,
    ...fila.datos,
  };
}

export async function fetchExcelValidRows(
  guidImportacion: string,
): Promise<Array<{ numeroFilaExcel: number; estadoFila: string; datos: Record<string, unknown> }>> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/filas/validas`,
    { headers: await authHeaders('application/json') },
  );
  const payload = await parseEnvelope<{
    items: Array<{ numeroFilaExcel: number; estadoFila: string; datos: Record<string, unknown> }>;
    total: number;
  }>(response);

  return payload.resultado.items ?? [];
}

export async function downloadExcelErrorsExport(guidImportacion: string): Promise<{ blob: Blob; fileName: string }> {
  const response = await fetch(
    `${getApiBaseUrl()}/excel-import/lotes/${encodeURIComponent(guidImportacion)}/export-errores`,
    { headers: await authHeaders() },
  );

  if (!response.ok) {
    const payload = (await response.json()) as Envelope<unknown>;
    throw new Error(payload.respuesta ?? 'excelImport.exportErrorsFailed');
  }

  const disposition = response.headers.get('Content-Disposition') ?? '';
  const match = disposition.match(/filename="([^"]+)"/);
  const fileName = match?.[1] ?? 'errores.xlsx';

  return { blob: await response.blob(), fileName };
}

export function buildHostResultFromLot(
  lot: ExcelImportLotSummary,
  validRows: Array<Record<string, unknown>>,
): ExcelImportHostResult {
  return {
    guidImportacion: lot.guidImportacion,
    codigoProceso: lot.codigoProceso ?? '',
    validRows,
    meta: {
      totalFilas: lot.cantidadFilasLeidas,
      filasValidas: lot.cantidadFilasValidas,
      filasConError: lot.cantidadFilasConError,
      permiteProcesamientoParcial: lot.permiteProcesamientoParcial ?? false,
      estadoImportacion: lot.estadoImportacion,
      nombreArchivoOriginal: lot.archivoOriginalNombre ?? '',
    },
  };
}
