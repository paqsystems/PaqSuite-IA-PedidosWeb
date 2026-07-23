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

/** Rutas adicionales v3 (`v1.2.2-mobile`). */
export const mobileV3AdditionalRoutePrefixes = ['/pedidos/carga'] as const;

/** Rutas MVP PedidosWeb habilitadas en native v3 (`v1.2.2-mobile`). */
export const mobileV3AllowedRoutePrefixes = [
  ...mobileV2AllowedRoutePrefixes,
  ...mobileV3AdditionalRoutePrefixes,
] as const;

/** Rutas transversales permitidas en shell native (fuera del menú MVP). */
export const mobileTransversalRoutes = ['/change-password', '/preferences'] as const;

export function isPedidosWebRouteAllowedOnMobileV2(routePath: string): boolean {
  if (!routePath) {
    return false;
  }

  return mobileV2AllowedRoutePrefixes.some((allowedRoute) => routePath.startsWith(allowedRoute));
}

export function isPedidosWebRouteAllowedOnMobileV3(routePath: string): boolean {
  if (!routePath) {
    return false;
  }

  return mobileV3AllowedRoutePrefixes.some((allowedRoute) => routePath.startsWith(allowedRoute));
}

export function isRouteAllowedOnMobileApp(routePath: string): boolean {
  if (mobileTransversalRoutes.some((route) => routePath.startsWith(route))) {
    return true;
  }

  if (!isRouteAllowedOnMobile(routePath)) {
    return false;
  }

  return isPedidosWebRouteAllowedOnMobileV3(routePath);
}

function filterMenuNodeForMobileV3(item: MenuNode): MenuNode | null {
  if (item.nodeType === 'group') {
    const children = item.children
      .map((child) => filterMenuNodeForMobileV3(child))
      .filter((child): child is MenuNode => child !== null);

    if (children.length === 0) {
      return null;
    }

    return {
      ...item,
      children,
    };
  }

  if (!item.routePath || !isPedidosWebRouteAllowedOnMobileV3(item.routePath)) {
    return null;
  }

  if (!isRouteAllowedOnMobile(item.routePath)) {
    return null;
  }

  return item;
}

export function filterMenuTreeForMobileV3(items: MenuNode[]): MenuNode[] {
  return items
    .map((item) => filterMenuNodeForMobileV3(item))
    .filter((item): item is MenuNode => item !== null);
}

/** @deprecated Usar filterMenuTreeForMobileV3 */
export function filterMenuTreeForMobileV2(items: MenuNode[]): MenuNode[] {
  return filterMenuTreeForMobileV3(items);
}

export function getMobileDefaultRoute(): string {
  return mobileV3AllowedRoutePrefixes[0];
}

/** @deprecated Usar mobileV3AllowedRoutePrefixes */
export const mobileV1AllowedRoutes = ['/consultas/stock'] as const;

/** @deprecated Usar filterMenuTreeForMobileV3 */
export function filterMenuTreeForMobileV1(items: MenuNode[]): MenuNode[] {
  return filterMenuTreeForMobileV3(items);
}
