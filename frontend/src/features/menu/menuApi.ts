import { apiRequest } from '../../shared/http/client';

export type MenuNode = {
  id: number;
  menuKey: string;
  labelKey: string;
  text: string;
  routePath: string | null;
  procedimiento: string;
  tipoProceso?: string | null;
  order: number;
  nodeType: 'group' | 'process';
  children: MenuNode[];
};

/** Convierte resultado de /user/menu (array, lista PHP-objeto o { items }) a array. */
export function coerceMenuList(raw: unknown): unknown[] {
  if (Array.isArray(raw)) {
    return raw;
  }

  if (raw && typeof raw === 'object') {
    const withItems = raw as { items?: unknown };
    if (Array.isArray(withItems.items)) {
      return withItems.items;
    }

    return Object.values(raw as Record<string, unknown>);
  }

  return [];
}

export function normalizeMenuNodes(raw: unknown): MenuNode[] {
  return coerceMenuList(raw)
    .map((item) => normalizeMenuNode(item))
    .filter((item): item is MenuNode => item !== null);
}

function normalizeMenuNode(raw: unknown): MenuNode | null {
  if (!raw || typeof raw !== 'object') {
    return null;
  }

  const node = raw as Record<string, unknown>;
  const menuKey = String(node.menuKey ?? '').trim();
  if (menuKey === '') {
    return null;
  }

  const nodeType = node.nodeType === 'group' || node.nodeType === 'process' ? node.nodeType : 'process';
  const routePathRaw = node.routePath;
  const routePath =
    typeof routePathRaw === 'string' && routePathRaw.trim() !== '' ? routePathRaw.trim() : null;

  return {
    id: Number(node.id) || 0,
    menuKey,
    labelKey: String(node.labelKey ?? `menu.${menuKey}`),
    text: String(node.text ?? menuKey),
    routePath,
    procedimiento: String(node.procedimiento ?? ''),
    tipoProceso:
      node.tipoProceso === null || node.tipoProceso === undefined
        ? null
        : String(node.tipoProceso),
    order: Number(node.order) || 0,
    nodeType,
    children: normalizeMenuNodes(node.children),
  };
}

export async function fetchUserMenu(): Promise<MenuNode[]> {
  const envelope = await apiRequest<unknown>('/user/menu');
  return normalizeMenuNodes(envelope.resultado);
}
