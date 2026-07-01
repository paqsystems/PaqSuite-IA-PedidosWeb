import { describe, expect, it } from 'vitest';
import {
  filterMenuTreeForMobileV3,
  isPedidosWebRouteAllowedOnMobileV2,
  isPedidosWebRouteAllowedOnMobileV3,
  isRouteAllowedOnMobileApp,
  mobileV3AllowedRoutePrefixes,
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

describe('pedidosWebMobilePolicy v3', () => {
  it('permite rutas MVP consultas, listados y carga', () => {
    expect(isPedidosWebRouteAllowedOnMobileV3('/consultas/stock')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV3('/pedidos/ingresados')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV3('/pedidos/carga')).toBe(true);
    expect(isPedidosWebRouteAllowedOnMobileV3('/pedidos/carga?modo=nuevo')).toBe(true);
  });

  it('mantiene prefijos v2 sin carga', () => {
    expect(isPedidosWebRouteAllowedOnMobileV2('/pedidos/carga')).toBe(false);
    expect(isPedidosWebRouteAllowedOnMobileV2('/consultas/stock')).toBe(true);
  });

  it('permite carga en native app guard', () => {
    expect(isRouteAllowedOnMobileApp('/pedidos/carga')).toBe(true);
    expect(isRouteAllowedOnMobileApp('/dashboard')).toBe(false);
    expect(isRouteAllowedOnMobileApp('/admin/roles')).toBe(false);
  });

  it('permite change-password y preferences', () => {
    expect(isRouteAllowedOnMobileApp('/change-password')).toBe(true);
    expect(isRouteAllowedOnMobileApp('/preferences')).toBe(true);
  });

  it('filtra menú dejando rutas v3 incluyendo carga', () => {
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

    const filtered = filterMenuTreeForMobileV3(menu);
    expect(filtered).toHaveLength(1);
    expect(filtered[0]?.children).toHaveLength(2);
    expect(filtered[0]?.children.map((child) => child.routePath)).toEqual([
      '/consultas/stock',
      '/pedidos/carga',
    ]);
  });

  it('expone prefijos v3 alineados al SPEC', () => {
    expect(mobileV3AllowedRoutePrefixes).toContain('/pedidos/carga');
    expect(mobileV3AllowedRoutePrefixes).toContain('/consultas/historial');
  });
});
