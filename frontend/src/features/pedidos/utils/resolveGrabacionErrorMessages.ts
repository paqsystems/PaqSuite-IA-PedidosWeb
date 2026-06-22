import type { TFunction } from 'i18next';
import { ApiClientError } from '../../../shared/http/client';

type GrabacionErrorResultado = {
  errores?: unknown;
  fields?: Record<string, string[]>;
};

export function resolveGrabacionErrorMessages(error: unknown, t: TFunction): string[] {
  if (error instanceof ApiClientError) {
    const resultado = error.resultado as GrabacionErrorResultado | undefined;

    if (Array.isArray(resultado?.errores) && resultado.errores.length > 0) {
      return resultado.errores
        .filter((item): item is string => typeof item === 'string' && item.trim() !== '')
        .map((key) => translateGrabacionErrorKey(key, t));
    }

    if (resultado?.fields) {
      const fieldMessages = Object.values(resultado.fields)
        .flat()
        .filter((message): message is string => typeof message === 'string' && message.trim() !== '');

      if (fieldMessages.length > 0) {
        return fieldMessages;
      }
    }

    if (error.respuestaKey && error.respuestaKey !== 'request.failed') {
      return [translateGrabacionErrorKey(error.respuestaKey, t)];
    }
  }

  return [];
}

function translateGrabacionErrorKey(key: string, t: TFunction): string {
  const translated = t(key, { defaultValue: '' });

  if (translated !== '' && translated !== key) {
    return translated;
  }

  return key;
}
