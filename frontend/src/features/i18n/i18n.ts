import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import es from '../../locales/es.json';
import en from '../../locales/en.json';
import pt from '../../locales/pt.json';
import fr from '../../locales/fr.json';
import it from '../../locales/it.json';
import parametrosPedidosWebEn from '../../locales/parametros/pedidosWeb.en.json';
import parametrosPedidosWebIt from '../../locales/parametros/pedidosWeb.it.json';
import parametrosPedidosWebFr from '../../locales/parametros/pedidosWeb.fr.json';
import parametrosPedidosWebPt from '../../locales/parametros/pedidosWeb.pt.json';
import { defaultLocale } from './model/supportedLocales';
import { resolveInitialLocale } from './model/resolveInitialLocale';
import { syncDevExtremeLocale } from './syncDevExtremeLocale';

const initialLocale = resolveInitialLocale();

void i18n.use(initReactI18next).init({
  resources: {
    es: { translation: es },
    en: { translation: { ...en, ...parametrosPedidosWebEn } },
    pt: { translation: { ...pt, ...parametrosPedidosWebPt } },
    fr: { translation: { ...fr, ...parametrosPedidosWebFr } },
    it: { translation: { ...it, ...parametrosPedidosWebIt } },
  },
  lng: initialLocale,
  fallbackLng: defaultLocale,
  interpolation: {
    escapeValue: false,
  },
});

syncDevExtremeLocale(initialLocale);

export default i18n;
