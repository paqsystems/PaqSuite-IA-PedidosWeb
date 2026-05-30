export type SessionContext = {
  token?: string;
  user: {
    id: number;
    displayName: string;
    login: string;
  };
  functionalProfile: string;
  codCliente: string | null;
  codVendedor: string | null;
  locale: string;
  theme: string;
  firstLogin: boolean;
  security: {
    roles: string[];
    accesoTotal: boolean;
  };
};

export type ApiEnvelope<T> = {
  error: number;
  respuesta: string;
  resultado: T;
};

export type LoginPayload = {
  codigo: string;
  password: string;
};
