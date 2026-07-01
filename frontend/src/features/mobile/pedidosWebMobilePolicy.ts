import type { MenuNode } from '../menu/menuApi';
import { isRouteAllowedOnMobile } from './mobileMenuPolicy';

export const mobileV1AllowedRoutes = ['/consultas/stock'] as const;

export function isMenuRouteAllowedOnMobileV1(routePath: string | null): boolean {
  if (!routePath) {
    return false;
  }

  return mobileV1AllowedRoutes.some((allowedRoute) => routePath.startsWith(allowedRoute));
}

function filterMenuNodeForMobileV1(item: MenuNode): MenuNode | null {
  if (item.nodeType === 'group') {
    const children = item.children
      .map((child) => filterMenuNodeForMobileV1(child))
      .filter((child): child is MenuNode => child !== null);

    if (children.length === 0) {
      return null;
    }

    return {
      ...item,
      children,
    };
  }

  if (!item.routePath || !isMenuRouteAllowedOnMobileV1(item.routePath)) {
    return null;
  }

  if (!isRouteAllowedOnMobile(item.routePath)) {
    return null;
  }

  return item;
}

export function filterMenuTreeForMobileV1(items: MenuNode[]): MenuNode[] {
  return items
    .map((item) => filterMenuNodeForMobileV1(item))
    .filter((item): item is MenuNode => item !== null);
}

export function getMobileDefaultRoute(): string {
  return mobileV1AllowedRoutes[0];
}
