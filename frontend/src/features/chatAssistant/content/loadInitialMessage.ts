import initialMessageRaw from './asistente-ia-mensaje-inicial.md?raw';
import { replaceMessagePlaceholders } from '../utils/replaceMessagePlaceholders';
import { stripMarkdownDocumentTitle } from '../utils/stripMarkdownDocumentTitle';

export function loadInitialMessage(): string {
  const body = stripMarkdownDocumentTitle(initialMessageRaw);
  return replaceMessagePlaceholders(body);
}
