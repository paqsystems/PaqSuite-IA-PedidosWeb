import { describe, expect, it, vi } from 'vitest';
import { loadFilteredSelectBoxItems, tryAutoSelectSingleMatch } from './tryAutoSelectSingleMatch';

function createMockComponent(items: unknown[], options?: { isLoading?: () => boolean }) {
  const load = vi.fn().mockResolvedValue(items);

  return {
    getDataSource: () => ({
      load,
      items: () => items,
      isLoading: options?.isLoading,
    }),
    option: vi.fn(),
    load,
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

describe('loadFilteredSelectBoxItems', () => {
  it('espera la búsqueda en curso en lugar de disparar un segundo load', async () => {
    let loading = true;
    const items: { codArticulo: string }[] = [];

    const component = createMockComponent(items, {
      isLoading: () => loading,
    });

    const pending = loadFilteredSelectBoxItems(component);

    setTimeout(() => {
      items.push({ codArticulo: 'A001' });
      loading = false;
    }, 80);

    await expect(pending).resolves.toEqual([{ codArticulo: 'A001' }]);
    expect(component.load).not.toHaveBeenCalled();
  });
});
