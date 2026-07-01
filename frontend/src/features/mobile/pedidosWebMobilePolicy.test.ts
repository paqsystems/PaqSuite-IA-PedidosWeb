import { describe, expect, it } from 'vitest';
import {
  filterMenuTreeForMobileV2,
  isPedidosWebRouteAllowedOnMobileV2,
  isRouteAllowedOnMobileApp,
  mobileV2AllowedRoutePrefixes,
} from './pedidosWebMobilePolicy';
import type { MenuNode } from '../menu/menuApi';

function buildMenuNode(partial: Partial<MenuNode> & Pick<MenuNode, 'id' | 'nodeType' | 'children'>): MenuNode {
  return {
    menuKey: partial.menuKey ?? 'test',
    labelKey: partial.labelKey ?? 'test',
    text: partial.text ?? 'Test',
    routePath: partial.routePath ?? null,
    procedimiento: partial.procedimiento ?? '',
    order: partial.order ?? 0,
    ...partial,
  };
}

describe('pedidosWebMobilePolicy v2', () => {
  it('permite rutas MVP consultas y listados', () => {
    expect(isPedidosWebRouteAllowedOnMobileV2('/consultas/stock')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV2('/consultas/deuda')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV2('/pedidos/ingresados')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV2('/general/parametros')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV2('/integracion/logs')).toBe(true);
  });

  it('bloquea carga y dashboard en native app', () => {
    expect(isRouteAllowedOnMobileApp('/pedidos/carga')).toBe(false);
    expect(isRouteAllowedOnMobileApp('/dashboard')).toBe(false);
    expect(isRouteAllowedOnMobileApp('/admin/roles')).toBe(false);
  });

  it('permite change-password y preferences', () => {
    expect(isRouteAllowedOnMobileApp('/change-password')).toBe(true);
    expect(isRouteAllowedOnMobileApp('/preferences')).toBe(true);
  });

  it('filtra menú dejando solo rutas v2', () => {
    const menu: MenuNode[] = [
      buildMenuNode({
        id: 1,
        nodeType: 'group',
        children: [
          buildMenuNode({
            id: 2,
            nodeType: 'process',
            routePath: '/consultas/stock',
            children: [],
          }),
          buildMenuNode({
            id: 3,
            nodeType: 'process',
            routePath: '/pedidos/carga',
            children: [],
          }),
        ],
      }),
    ];

    const filtered = filterMenuTreeForMobileV2(menu);
    expect(filtered).toHaveLength(1);
    expect(filtered[0]?.children).toHaveLength(1);
    expect(filtered[0]?.children[0]?.routePath).toBe('/consultas/stock');
  });

  it('expone prefijos v2 alineados al SPEC', () => {
    expect(mobileV2AllowedRoutePrefixes).toContain('/consultas/historial');
    expect(mobileV2AllowedRoutePrefixes).toContain('/presupuestos/tratativas');
  });
});
