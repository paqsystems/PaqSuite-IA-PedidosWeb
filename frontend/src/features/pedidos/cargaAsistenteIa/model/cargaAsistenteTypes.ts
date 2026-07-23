export type CargaAsistenteModality = 'texto' | 'audio' | 'imagen';

export type CargaAsistenteDraftContext = {
  modo: string;
  perfilUsuario: 'V' | 'S' | 'C';
  codCliente: string | null;
  cabecera: Record<string, unknown>;
  renglones: Array<{
    renglon?: number;
    codArticulo: string;
    cantidad: number;
    precio?: number;
    porcBonif?: number;
    descripcion?: string;
  }>;
  readOnly: boolean;
  codLista: number;
};

export type CargaAsistentePendingChoice = {
  kind: string;
  options?: Array<{ n: number; label: string; code?: string }>;
  [key: string]: unknown;
} | null;

export type CargaAsistenteAction = {
  action: string;
  payload: Record<string, unknown>;
  resultado: string;
};

export type CargaAsistenteTurnResult = {
  replyText: string;
  actions: CargaAsistenteAction[];
  pendingChoice: CargaAsistentePendingChoice;
  configurationRequired: boolean;
};

export type CargaAsistenteImagePayload = {
  fileName: string;
  mimeType: string;
  contentBase64: string;
};
