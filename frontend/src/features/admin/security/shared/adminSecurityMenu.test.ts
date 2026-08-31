import { describe, expect, it } from 'vitest';
import type { MenuNode } from '../../../menu/menuApi';
import { filterMenuTreeForAdminSecurity, isAdminSecurityMenuNode } from './adminSecurityMenu';

function menuNode(partial: Partial<MenuNode> & Pick<MenuNode, 'menuKey' | 'text' | 'nodeType'>): MenuNode {
  return {
    id: partial.id ?? 1,
    menuKey: partial.menuKey,
    labelKey: partial.labelKey ?? `menu.${partial.menuKey}`,
    text: partial.text,
    routePath: partial.routePath ?? null,
    procedimiento: partial.procedimiento ?? '',
    tipoProceso: partial.tipoProceso ?? null,
    order: partial.order ?? 0,
    nodeType: partial.nodeType,
    children: partial.children ?? [],
  };
}

describe('adminSecurityMenu', () => {
  it('detecta nodos Framework y MVP de roles/permisos', () => {
    expect(
      isAdminSecurityMenuNode({
        routePath: '/admin/roles',
        procedimiento: 'seguridad_roles',
      }),
    ).toBe(true);
    expect(
      isAdminSecurityMenuNode({
        routePath: '/admin/permisos',
        procedimiento: 'pw_adminpermisos',
      }),
    ).toBe(true);
    expect(
      isAdminSecurityMenuNode({
        routePath: '/admin/usuarios',
        procedimiento: 'seguridad_usuarios',
      }),
    ).toBe(false);
  });

  it('oculta roles/permisos cuando el flag esta deshabilitado', () => {
    const tree: MenuNode[] = [
      menuNode({
        menuKey: 'administracion',
        text: 'Administracion',
        nodeType: 'group',
        children: [
          menuNode({
            id: 2,
            menuKey: 'seguridad',
            text: 'Seguridad',
            nodeType: 'group',
            children: [
              menuNode({
                id: 3,
                menuKey: 'roles',
                text: 'Roles',
                nodeType: 'process',
                routePath: '/admin/roles',
                procedimiento: 'seguridad_roles',
              }),
              menuNode({
                id: 4,
                menuKey: 'usuarios',
                text: 'Usuarios',
                nodeType: 'process',
                routePath: '/admin/usuarios',
                procedimiento: 'seguridad_usuarios',
              }),
            ],
          }),
        ],
      }),
    ];

    const filtered = filterMenuTreeForAdminSecurity(tree, false);
    expect(filtered).toHaveLength(1);
    expect(filtered[0].children[0].children).toHaveLength(1);
    expect(filtered[0].children[0].children[0].routePath).toBe('/admin/usuarios');
  });

  it('no filtra cuando el flag esta habilitado', () => {
    const tree: MenuNode[] = [
      menuNode({
        menuKey: 'roles',
        text: 'Roles',
        nodeType: 'process',
        routePath: '/admin/roles',
        procedimiento: 'seguridad_roles',
      }),
    ];

    expect(filterMenuTreeForAdminSecurity(tree, true)).toEqual(tree);
  });

  it('no revienta si children falta o no es array', () => {
    const brokenGroup = {
      id: 1,
      menuKey: 'grupo',
      labelKey: 'menu.grupo',
      text: 'Grupo',
      routePath: null,
      procedimiento: 'grp_x',
      tipoProceso: 'G',
      order: 1,
      nodeType: 'group' as const,
      children: undefined,
    } as unknown as MenuNode;

    expect(() => filterMenuTreeForAdminSecurity([brokenGroup], false)).not.toThrow();
    expect(filterMenuTreeForAdminSecurity([brokenGroup], false)).toEqual([]);
    expect(filterMenuTreeForAdminSecurity(null as unknown as MenuNode[], false)).toEqual([]);
  });
});
