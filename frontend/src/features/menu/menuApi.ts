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

export async function fetchUserMenu(): Promise<MenuNode[]> {
  const envelope = await apiRequest<MenuNode[]>('/user/menu');
  return envelope.resultado;
}
