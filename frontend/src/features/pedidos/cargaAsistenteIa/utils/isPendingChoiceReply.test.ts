import { describe, expect, it } from 'vitest';

import type { CargaAsistentePendingChoice } from '../model/cargaAsistenteTypes';
import { isPendingChoiceReply } from './isPendingChoiceReply';

const renglonChoice: CargaAsistentePendingChoice = {
  kind: 'renglonExistente',
  operation: 'remove',
  options: [{ n: 1, label: 'A', code: 'A', renglon: 1 }],
};

describe('isPendingChoiceReply', () => {
  it('treats 1–10 as choice reply for any pending kind', () => {
    expect(isPendingChoiceReply('1', renglonChoice)).toBe(true);
    expect(isPendingChoiceReply('  10  ', renglonChoice)).toBe(true);
  });

  it('does not treat free text as choice when kind is not changeClienteConfirm', () => {
    expect(isPendingChoiceReply('elimina arroz', renglonChoice)).toBe(false);
    expect(isPendingChoiceReply('agregá 5 unidades', renglonChoice)).toBe(false);
  });

  it('accepts sí/no for changeClienteConfirm', () => {
    const pending: CargaAsistentePendingChoice = { kind: 'changeClienteConfirm' };
    expect(isPendingChoiceReply('sí', pending)).toBe(true);
    expect(isPendingChoiceReply('NO', pending)).toBe(true);
    expect(isPendingChoiceReply('confirmo', pending)).toBe(true);
  });
});