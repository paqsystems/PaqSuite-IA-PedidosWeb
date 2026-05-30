/// <reference types="vite/client" />

declare module '*.module.css' {
  const classes: { readonly [key: string]: string };
  export default classes;
}

interface ImportMetaEnv {
  readonly VITE_API_BASE_URL?: string;
  readonly VITE_TENANT_DEFAULT_CLIENT?: string;
  readonly VITE_APP_VERSION?: string;
  readonly VITE_DEVEXTREME_LICENSE?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
