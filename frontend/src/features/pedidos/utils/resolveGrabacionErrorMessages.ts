import type { TFunction } from 'i18next';
import { ApiClientError } from '../../../shared/http/client';

type GrabacionErrorResultado = {
  errores?: unknown;
  fields?: Record<string, string[]>;
};

export function resolveGrabacionErrorMessages(error: unknown, t: TFunction): string[] {
  if (error instanceof ApiClientError) {
    const resultado = error.resultado;

    if (typeof resultado === 'string' && resultado.trim() !== '') {
      return [resultado];
    }

    const grabacionResultado = resultado as GrabacionErrorResultado | undefined;

    if (Array.isArray(grabacionResultado?.errores) && grabacionResultado.errores.length > 0) {
      return grabacionResultado.errores
        .filter((item): item is string => typeof item === 'string' && item.trim() !== '')
        .map((key) => translateGrabacionErrorKey(key, t));
    }

    if (grabacionResultado?.fields) {
      const fieldMessages = Object.values(grabacionResultado.fields)
        .flat()
        .filter((message): message is string => typeof message === 'string' && message.trim() !== '');

      if (fieldMessages.length > 0) {
        return fieldMessages;
      }
    }

    if (error.respuestaKey && error.respuestaKey !== 'request.failed') {
      return [translateGrabacionErrorKey(error.respuestaKey, t)];
    }

    if (grabacionResultado && typeof grabacionResultado === 'object' && 'message' in grabacionResultado) {
      const message = (grabacionResultado as { message?: unknown }).message;
      if (typeof message === 'string' && message.trim() !== '') {
        return [message];
      }
    }
  }

  if (error instanceof Error && error.message.trim() !== '' && error.message !== 'request.failed') {
    return [error.message];
  }

  return [];
}

function translateGrabacionErrorKey(key: string, t: TFunction): string {
  const translated = t(key, { defaultValue: '' });

  if (translated !== '' && translated !== key) {
    return translated;
  }

  if (key.startsWith('business.')) {
    return key
      .slice('business.'.length)
      .replace(/([A-Z])/g, ' $1')
      .replace(/^./, (char) => char.toUpperCase());
  }

  return key;
}
