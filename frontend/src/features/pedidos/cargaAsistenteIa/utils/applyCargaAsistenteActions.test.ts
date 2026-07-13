import { describe, expect, it } from 'vitest';
import { applyCargaAsistenteActions } from './applyCargaAsistenteActions';

describe('applyCargaAsistenteActions', () => {
  it('applies clearDraft then selectCliente sequentially', async () => {
    const calls: string[] = [];

    await applyCargaAsistenteActions(
      [
        { action: 'clearDraftForClienteChange', payload: {}, resultado: 'ok' },
        { action: 'selectCliente', payload: { codCliente: 'C1' }, resultado: 'ok' },
        { action: 'noop', payload: {}, resultado: 'ok' },
      ],
      {
        clearDraft: () => {
          calls.push('clear');
        },
        selectCliente: async (codCliente) => {
          calls.push(`select:${codCliente}`);
        },
        addRenglon: () => {
          calls.push('add');
        },
        updateCabeceraField: () => {
          calls.push('field');
        },
        grabarPedido: () => {
          calls.push('pedido');
        },
        grabarPresupuesto: () => {
          calls.push('presupuesto');
        },
      },
    );

    expect(calls).toEqual(['clear', 'select:C1']);
  });
});
