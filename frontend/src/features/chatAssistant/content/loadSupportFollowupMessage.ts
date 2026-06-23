import supportFollowupMessageRaw from './asistente-ia-mensaje-cierre-soporte.md?raw';
import { replaceMessagePlaceholders } from '../utils/replaceMessagePlaceholders';
import { stripMarkdownDocumentTitle } from '../utils/stripMarkdownDocumentTitle';

export function loadSupportFollowupMessage(): string {
  const body = stripMarkdownDocumentTitle(supportFollowupMessageRaw);
  return replaceMessagePlaceholders(body);
}
