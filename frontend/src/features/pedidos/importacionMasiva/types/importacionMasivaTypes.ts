import type { ComprobanteCabecera } from '../../types/comprobanteCabecera';
import type { ComprobanteRenglon } from '../../api/comprobanteApi';

export type ImportacionMasivaGrupoApi = {
  idGrupo: string;
  clave: {
    codCliente: string;
    codVended: string;
    nivel: number;
  };
  cabecera: Record<string, unknown>;
  renglones: Array<Record<string, unknown>>;
  vendedor: {
    codVended: string;
    nombre: string;
  };
};

export type BorradorFila = {
  idInterno: string;
  esPedido: boolean;
  cabecera: ComprobanteCabecera;
  renglones: ComprobanteRenglon[];
  errorGrabacion: string | null;
  cantidadRenglones: number;
  totalImporte: number;
};

export type ImportacionMasivaBorradorSnapshot = {
  filas: BorradorFila[];
};

export type ImportacionMasivaProgreso = {
  x: number;
  n: number;
};

export type ImportacionMasivaGrabacionResumen = {
  ok: number;
  err: number;
};

export type ImportacionMasivaGridRow = BorradorFila & {
  tipoComprobanteLabel: string;
};
