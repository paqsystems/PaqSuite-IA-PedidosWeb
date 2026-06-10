import config from 'devextreme/core/config';

const licenseKey = String(import.meta.env.VITE_DEVEXTREME_LICENSE ?? '').trim();

if (import.meta.env.PROD && licenseKey === '') {
  throw new Error(
    '[DevExtreme] Licencia faltante en produccion. Configure VITE_DEVEXTREME_LICENSE para generar un build valido.',
  );
}

config({ licenseKey });
