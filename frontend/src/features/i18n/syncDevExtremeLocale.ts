import { loadMessages, locale } from 'devextreme/localization';
import esMessages from 'devextreme/localization/messages/es.json';
import enMessages from 'devextreme/localization/messages/en.json';
import ptMessages from 'devextreme/localization/messages/pt.json';
import frMessages from 'devextreme/localization/messages/fr.json';
import itMessages from 'devextreme/localization/messages/it.json';
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

  Object.entries(devExtremeMessagesByLocale).forEach(([localeCode, messages]) => {
    loadMessages({ [localeCode]: messages });
  });

  messagesLoaded = true;
}

export function syncDevExtremeLocale(localeCode: string): void {
  ensureDevExtremeMessagesLoaded();

  if (isSupportedLocale(localeCode)) {
    locale(localeCode);
  }
}
