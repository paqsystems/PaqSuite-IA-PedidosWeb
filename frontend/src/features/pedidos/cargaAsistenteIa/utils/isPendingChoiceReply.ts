import type { CargaAsistentePendingChoice } from '../model/cargaAsistenteTypes';

/**
 * True when the user message should be sent with pendingChoice (numeric option or change-cliente confirm).
 */
export function isPendingChoiceReply(
  message: string,
  pendingChoice: CargaAsistentePendingChoice,
): boolean {
  if (/^\s*([1-9]|10)\s*$/u.test(message)) {
    return true;
  }

  if (pendingChoice?.kind !== 'changeClienteConfirm') {
    return false;
  }

  return /^\s*(?:s[ií]|no|confirmo|aceptado|cancelar)\s*$/iu.test(message);
}