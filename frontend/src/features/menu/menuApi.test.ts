import { describe, expect, it } from 'vitest';
import { coerceMenuList, normalizeMenuNodes } from './menuApi';

describe('menuApi normalize', () => {
  it('normaliza array y children ausentes', () => {
    const nodes = normalizeMenuNodes([
      {
        id: 1,
        menuKey: 'grupo',
        text: 'Grupo',
        nodeType: 'group',
        order: 1,
      },
    ]);

    expect(nodes).toHaveLength(1);
    expect(nodes[0].children).toEqual([]);
    expect(nodes[0].labelKey).toBe('menu.grupo');
  });

  it('acepta objeto con claves numericas (JSON PHP no-list)', () => {
    const raw = {
      0: { id: 1, menuKey: 'a', text: 'A', nodeType: 'process', order: 1 },
      1: { id: 2, menuKey: 'b', text: 'B', nodeType: 'process', order: 2 },
    };

    expect(coerceMenuList(raw)).toHaveLength(2);
    expect(normalizeMenuNodes(raw).map((n) => n.menuKey)).toEqual(['a', 'b']);
  });

  it('acepta wrapper items', () => {
    expect(
      normalizeMenuNodes({
        items: [{ id: 1, menuKey: 'x', text: 'X', nodeType: 'process' }],
      }),
    ).toHaveLength(1);
  });
});
