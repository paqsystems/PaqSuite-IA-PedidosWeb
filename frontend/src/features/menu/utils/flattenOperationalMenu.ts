import type { MenuNode } from '../menuApi';

export function flattenOperationalMenu(menuItems: MenuNode[]): MenuNode[] {
  const flattened: MenuNode[] = [];

  function walk(nodes: MenuNode[]) {
    for (const node of nodes) {
      if (node.nodeType === 'process') {
        flattened.push({
          ...node,
          children: [],
        });
      }

      if (node.children.length > 0) {
        walk(node.children);
      }
    }
  }

  walk(menuItems);

  return flattened.sort((left, right) => left.order - right.order);
}

export function collectAncestorMenuKeys(
  menuItems: MenuNode[],
  activeRoutePath: string,
): string[] {
  const ancestors: string[] = [];

  function walk(nodes: MenuNode[], path: string[]): boolean {
    for (const node of nodes) {
      const nextPath = [...path, node.menuKey];

      if (node.routePath === activeRoutePath) {
        ancestors.push(...path);
        return true;
      }

      if (node.children.length > 0 && walk(node.children, nextPath)) {
        return true;
      }
    }

    return false;
  }

  walk(menuItems, []);

  return ancestors;
}

export function findActiveMenuKey(
  menuItems: MenuNode[],
  activeRoutePath: string,
): string | null {
  for (const node of menuItems) {
    if (node.routePath === activeRoutePath) {
      return node.menuKey;
    }

    if (node.children.length > 0) {
      const childMatch = findActiveMenuKey(node.children, activeRoutePath);
      if (childMatch !== null) {
        return childMatch;
      }
    }
  }

  return null;
}

export function collectAllGroupMenuKeys(menuItems: MenuNode[]): string[] {
  const keys: string[] = [];

  function walk(nodes: MenuNode[]) {
    for (const node of nodes) {
      if (node.nodeType === 'group') {
        keys.push(node.menuKey);
      }

      if (node.children.length > 0) {
        walk(node.children);
      }
    }
  }

  walk(menuItems);

  return keys;
}
