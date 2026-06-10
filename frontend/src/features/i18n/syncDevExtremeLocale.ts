/**
 * Sincroniza locale DevExtreme con i18n de la app.
 * @see docs/00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md — §3 loadMessages (no doble anidar locale)
 */
import { loadMessages, locale } from 'devextreme/localization';
import esMessages from 'devextreme/localization/messages/es.json';
import enMessages from 'devextreme/localization/messages/en.json';
import ptMessages from 'devextreme/localization/messages/pt.json';
import frMessages from 'devextreme/localization/messages/fr.json';
import itMessages from 'devextreme/localization/messages/it.json';
import { getGridDevExtremeMessageOverrides } from './gridDevExtremeMessages';
import { isSupportedLocale } from './model/supportedLocales';

const devExtremeMessagesByLocale: Record<string, object> = {
  es: esMessages,
  en: enMessages,
  pt: ptMessages,
  fr: frMessages,
  it: itMessages,
};

let messagesLoaded = false;

function ensureDevExtremeMessagesLoaded(): void {
  if (messagesLoaded) {
    return;
  }

  Object.values(devExtremeMessagesByLocale).forEach((messages) => {
    loadMessages(messages);
  });

  messagesLoaded = true;
}

export function syncDevExtremeLocale(localeCode: string): void {
  ensureDevExtremeMessagesLoaded();

  if (!isSupportedLocale(localeCode)) {
    return;
  }

  const overrides = getGridDevExtremeMessageOverrides(localeCode);
  if (Object.keys(overrides).length > 0) {
    loadMessages({ [localeCode]: overrides });
  }

  locale(localeCode);
}
