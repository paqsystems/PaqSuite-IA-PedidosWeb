import type { MenuNode } from '../../../menu/menuApi';

const adminSecurityRoutePrefixes = ['/admin/roles', '/admin/permisos'] as const;

const adminSecurityProcedimientos = new Set([
  'pw_adminroles',
  'pw_adminpermisos',
  'seguridad_roles',
  'seguridad_permisos',
]);

export function isAdminSecurityMenuNode(node: Pick<MenuNode, 'routePath' | 'procedimiento'>): boolean {
  const procedimiento = (node.procedimiento ?? '').trim().toLowerCase();
  if (procedimiento !== '' && adminSecurityProcedimientos.has(procedimiento)) {
    return true;
  }

  const routePath = (node.routePath ?? '').trim();
  return adminSecurityRoutePrefixes.some((prefix) => routePath === prefix || routePath.startsWith(`${prefix}/`));
}

export function filterMenuTreeForAdminSecurity(items: MenuNode[], securityAdminEnabled: boolean): MenuNode[] {
  const safeItems = Array.isArray(items) ? items : [];

  if (securityAdminEnabled) {
    return safeItems;
  }

  return safeItems
    .map((item) => filterAdminSecurityMenuNode(item))
    .filter((item): item is MenuNode => item !== null);
}

function filterAdminSecurityMenuNode(item: MenuNode): MenuNode | null {
  if (item.nodeType === 'group') {
    const childList = Array.isArray(item.children) ? item.children : [];
    const children = childList
      .map((child) => filterAdminSecurityMenuNode(child))
      .filter((child): child is MenuNode => child !== null);

    if (children.length === 0) {
      return null;
    }

    return {
      ...item,
      children,
    };
  }

  if (isAdminSecurityMenuNode(item)) {
    return null;
  }

  return item;
}
