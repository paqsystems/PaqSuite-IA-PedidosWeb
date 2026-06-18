export type ExcelImportHostResult = {
  guidImportacion: string;
  codigoProceso: string;
  validRows: Array<Record<string, unknown>>;
  meta: {
    totalFilas: number;
    filasValidas: number;
    filasConError: number;
    permiteProcesamientoParcial: boolean;
    estadoImportacion: string;
    nombreArchivoOriginal: string;
  };
};

export type ExcelImportHostToolbarProps = {
  codigoProceso: string;
  disabled?: boolean;
  onComplete: (result: ExcelImportHostResult) => void;
  onCancel?: () => void;
};

export type ExcelImportHostModalPhase = 'upload' | 'structuralError' | 'errors' | 'processing';
