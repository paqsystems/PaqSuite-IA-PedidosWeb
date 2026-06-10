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
  inactivityTimeoutMinutes: number;
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

export type ForgotPasswordPayload = {
  email: string;
  locale?: string;
};

export type ResetPasswordPayload = {
  token: string;
  newPassword: string;
  newPasswordConfirmation: string;
};
