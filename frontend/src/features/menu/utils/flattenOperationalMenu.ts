import type { MenuNode } from '../menuApi';

function childNodesOf(node: MenuNode): MenuNode[] {
  return Array.isArray(node.children) ? node.children : [];
}

export function flattenOperationalMenu(menuItems: MenuNode[]): MenuNode[] {
  const flattened: MenuNode[] = [];

  function walk(nodes: MenuNode[]) {
    if (!Array.isArray(nodes)) {
      return;
    }

    for (const node of nodes) {
      if (node.nodeType === 'process') {
        flattened.push({
          ...node,
          children: [],
        });
      }

      const children = childNodesOf(node);
      if (children.length > 0) {
        walk(children);
      }
    }
  }

  walk(Array.isArray(menuItems) ? menuItems : []);

  return flattened.sort((left, right) => left.order - right.order);
}

export function collectAncestorMenuKeys(
  menuItems: MenuNode[],
  activeRoutePath: string,
): string[] {
  const ancestors: string[] = [];

  function walk(nodes: MenuNode[], path: string[]): boolean {
    if (!Array.isArray(nodes)) {
      return false;
    }

    for (const node of nodes) {
      const nextPath = [...path, node.menuKey];

      if (node.routePath === activeRoutePath) {
        ancestors.push(...path);
        return true;
      }

      const children = childNodesOf(node);
      if (children.length > 0 && walk(children, nextPath)) {
        return true;
      }
    }

    return false;
  }

  walk(Array.isArray(menuItems) ? menuItems : [], []);

  return ancestors;
}

export function findActiveMenuKey(
  menuItems: MenuNode[],
  activeRoutePath: string,
): string | null {
  if (!Array.isArray(menuItems)) {
    return null;
  }

  for (const node of menuItems) {
    if (node.routePath === activeRoutePath) {
      return node.menuKey;
    }

    const children = childNodesOf(node);
    if (children.length > 0) {
      const childMatch = findActiveMenuKey(children, activeRoutePath);
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
    if (!Array.isArray(nodes)) {
      return;
    }

    for (const node of nodes) {
      if (node.nodeType === 'group') {
        keys.push(node.menuKey);
      }

      const children = childNodesOf(node);
      if (children.length > 0) {
        walk(children);
      }
    }
  }

  walk(Array.isArray(menuItems) ? menuItems : []);

  return keys;
}
