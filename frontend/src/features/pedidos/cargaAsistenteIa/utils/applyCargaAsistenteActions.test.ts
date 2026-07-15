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

  it('applies selectCliente then applyImageExtract so renglones survive cliente change', async () => {
    const calls: string[] = [];

    await applyCargaAsistenteActions(
      [
        { action: 'selectCliente', payload: { codCliente: 'C2' }, resultado: 'ok' },
        {
          action: 'applyImageExtract',
          payload: { renglonesValidos: [{ codArticulo: 'A1', cantidad: 2 }] },
          resultado: 'ok',
        },
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
        applyImageExtract: () => {
          calls.push('imageExtract');
        },
      },
    );

    expect(calls).toEqual(['select:C2', 'imageExtract']);
  });
});
