import config from 'devextreme/core/config';

config({
  licenseKey: String(import.meta.env.VITE_DEVEXTREME_LICENSE ?? '').trim(),
});
