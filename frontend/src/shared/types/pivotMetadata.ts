export type PivotCampoMetadata = {
  campoId: string;
  dataField: string;
  caption: string;
  tipoDato: string;
  rolCampo: string;
  rolesPermitidos: string[];
  agregacionDefault?: string | null;
  agregacionesPermitidas?: string[] | null;
  formato?: Record<string, unknown> | null;
};

export type PivotFiltroGeneral = {
  filtroId: string;
  dataField: string;
  caption: string;
  obligatorio?: boolean;
  tipoControl?: string;
};

export type PivotRestricciones = {
  maximoFilas: number;
  maximoColumnas: number;
  maximoMetricas: number;
  maximoRegistrosBase: number;
  bloquearSiExcedeVolumen: boolean;
  requiereFiltroPrevio: boolean;
};

export type PivotMetadataResult = {
  consultaId: string;
  versionDefinicion: number;
  pivotHabilitado: boolean;
  admiteDrilldown: boolean;
  configuracionGeneral: Record<string, unknown>;
  pivotBase: Record<string, unknown>;
  campos: PivotCampoMetadata[];
  filtrosGenerales: PivotFiltroGeneral[];
  restricciones: PivotRestricciones;
  exportacion: Record<string, unknown>;
  persistencia: Record<string, unknown>;
};

export type PivotDatasetRequest = {
  filtros?: Record<string, unknown>;
  pagina?: number;
  tamanoPagina?: number;
};

export type PivotDatasetResult = {
  items: Record<string, unknown>[];
  totalRegistros: number;
  truncado: boolean;
};

export type PivotStructureRequest = {
  filas?: string[];
  columnas?: string[];
  valores?: Array<{ campoId: string; agregacion: string }>;
  filtrosInternos?: unknown[];
};
