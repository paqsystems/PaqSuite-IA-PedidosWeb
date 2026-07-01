import type { MenuNode } from '../menu/menuApi';
import { isRouteAllowedOnMobile } from './mobileMenuPolicy';

/** Rutas MVP PedidosWeb habilitadas en native v2 (`v1.2.1-mobile`). */
export const mobileV2AllowedRoutePrefixes = [
  '/consultas/stock',
  '/consultas/deuda',
  '/consultas/cheques',
  '/consultas/historial',
  '/pedidos/detalle',
  '/pedidos/ingresados',
  '/pedidos/pendientes',
  '/presupuestos/ingresados',
  '/presupuestos/tratativas',
  '/general/parametros',
  '/integracion/logs',
] as const;

/** Rutas transversales permitidas en shell native (fuera del menú MVP). */
export const mobileTransversalRoutes = ['/change-password', '/preferences'] as const;

export function isPedidosWebRouteAllowedOnMobileV2(routePath: string): boolean {
  if (!routePath) {
    return false;
  }

  return mobileV2AllowedRoutePrefixes.some((allowedRoute) => routePath.startsWith(allowedRoute));
}

export function isRouteAllowedOnMobileApp(routePath: string): boolean {
  if (mobileTransversalRoutes.some((route) => routePath.startsWith(route))) {
    return true;
  }

  if (!isRouteAllowedOnMobile(routePath)) {
    return false;
  }

  return isPedidosWebRouteAllowedOnMobileV2(routePath);
}

function filterMenuNodeForMobileV2(item: MenuNode): MenuNode | null {
  if (item.nodeType === 'group') {
    const children = item.children
      .map((child) => filterMenuNodeForMobileV2(child))
      .filter((child): child is MenuNode => child !== null);

    if (children.length === 0) {
      return null;
    }

    return {
      ...item,
      children,
    };
  }

  if (!item.routePath || !isPedidosWebRouteAllowedOnMobileV2(item.routePath)) {
    return null;
  }

  if (!isRouteAllowedOnMobile(item.routePath)) {
    return null;
  }

  return item;
}

export function filterMenuTreeForMobileV2(items: MenuNode[]): MenuNode[] {
  return items
    .map((item) => filterMenuNodeForMobileV2(item))
    .filter((item): item is MenuNode => item !== null);
}

export function getMobileDefaultRoute(): string {
  return mobileV2AllowedRoutePrefixes[0];
}

/** @deprecated Usar filterMenuTreeForMobileV2 */
export const mobileV1AllowedRoutes = ['/consultas/stock'] as const;

/** @deprecated Usar filterMenuTreeForMobileV2 */
export function filterMenuTreeForMobileV1(items: MenuNode[]): MenuNode[] {
  return filterMenuTreeForMobileV2(items);
}
