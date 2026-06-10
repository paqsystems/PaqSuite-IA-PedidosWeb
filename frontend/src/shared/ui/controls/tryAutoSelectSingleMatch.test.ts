import { describe, expect, it, vi } from 'vitest';
import { tryAutoSelectSingleMatch } from './tryAutoSelectSingleMatch';

function createMockComponent(items: unknown[]) {
  return {
    getDataSource: () => ({
      load: vi.fn().mockResolvedValue(items),
      items: () => items,
    }),
    option: vi.fn(),
  };
}

describe('tryAutoSelectSingleMatch', () => {
  it('selecciona el valor cuando hay un único ítem', async () => {
    const component = createMockComponent([{ codCliente: 'A001', nombre: 'Alfa' }]);

    const value = await tryAutoSelectSingleMatch(component, 'codCliente');

    expect(value).toBe('A001');
  });

  it('no selecciona cuando hay cero o más de un ítem', async () => {
    const vacio = createMockComponent([]);
    const multiples = createMockComponent([
      { codCliente: 'A001' },
      { codCliente: 'B002' },
    ]);

    expect(await tryAutoSelectSingleMatch(vacio, 'codCliente')).toBeNull();
    expect(await tryAutoSelectSingleMatch(multiples, 'codCliente')).toBeNull();
  });
});
