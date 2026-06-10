import { describe, expect, it } from 'vitest';
import {
  collectAncestorMenuKeys,
  flattenOperationalMenu,
} from '../../src/features/menu/utils/flattenOperationalMenu';
import type { MenuNode } from '../../src/features/menu/menuApi';

const hierarchicalMenu: MenuNode[] = [
  {
    id: 1,
    menuKey: 'grupoPedidos',
    labelKey: 'menu.grupoPedidos',
    text: 'Pedidos',
    routePath: null,
    procedimiento: 'grupo_pedidos',
    order: 10,
    nodeType: 'group',
    children: [
      {
        id: 2,
        menuKey: 'pedidosIngresados',
        labelKey: 'menu.pedidosIngresados',
        text: 'Pedidos ingresados',
        routePath: '/pedidos/ingresados',
        procedimiento: 'pw_pedidosingresados',
        order: 20,
        nodeType: 'process',
        children: [],
      },
      {
        id: 3,
        menuKey: 'stock',
        labelKey: 'menu.stock',
        text: 'Stock',
        routePath: '/consultas/stock',
        procedimiento: 'pw_consultastock',
        order: 30,
        nodeType: 'process',
        children: [],
      },
    ],
  },
];

describe('flattenOperationalMenu', () => {
  it('devuelve solo nodos process ordenados por order', () => {
    const flattened = flattenOperationalMenu(hierarchicalMenu);

    expect(flattened.map((item) => item.menuKey)).toEqual([
      'pedidosIngresados',
      'stock',
    ]);
  });

  it('encuentra ancestros del item activo', () => {
    expect(collectAncestorMenuKeys(hierarchicalMenu, '/pedidos/ingresados')).toEqual([
      'grupoPedidos',
    ]);
  });
});
