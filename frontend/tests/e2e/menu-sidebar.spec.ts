import { test, expect } from '@playwright/test';
import { clickAvatarMenuItem } from './helpers/avatarMenu';

const acotadoMenu = [
  {
    id: 1,
    menuKey: 'cargaPedidosPresupuestos',
    labelKey: 'menu.cargaPedidosPresupuestos',
    text: 'Carga Pedidos',
    routePath: '/pedidos/carga',
    procedimiento: 'pw_cargapedidos',
    tipoProceso: 'P',
    order: 10,
    nodeType: 'process',
    children: [],
  },
  {
    id: 2,
    menuKey: 'pedidosIngresados',
    labelKey: 'menu.pedidosIngresados',
    text: 'Pedidos Ingresados',
    routePath: '/pedidos/ingresados',
    procedimiento: 'pw_pedidosingresados',
    tipoProceso: 'P',
    order: 30,
    nodeType: 'process',
    children: [],
  },
];

const supervisorMenu = [
  ...acotadoMenu,
  {
    id: 3,
    menuKey: 'stock',
    labelKey: 'menu.stock',
    text: 'Consulta Stock',
    routePath: '/consultas/stock',
    procedimiento: 'pw_consultastock',
    tipoProceso: 'P',
    order: 80,
    nodeType: 'process',
    children: [],
  },
];

const hierarchicalMenu = [
  {
    id: 10,
    menuKey: 'grupoConsultas',
    labelKey: 'menu.grupoConsultas',
    text: 'Consultas',
    routePath: null,
    procedimiento: 'grupo_consultas',
    tipoProceso: 'G',
    order: 50,
    nodeType: 'group',
    children: [
      {
        id: 11,
        menuKey: 'pedidosIngresados',
        labelKey: 'menu.pedidosIngresados',
        text: 'Pedidos Ingresados',
        routePath: '/pedidos/ingresados',
        procedimiento: 'pw_pedidosingresados',
        tipoProceso: 'P',
        order: 30,
        nodeType: 'process',
        children: [],
      },
    ],
  },
];

function buildSession(userId: number, login: string, displayName: string) {
  return {
    token: `token-${userId}`,
    user: { id: userId, displayName, login },
    functionalProfile: 'vendedor',
    codCliente: null,
    codVendedor: 'VENACOT01',
    locale: 'es-AR',
    theme: 'light',
    firstLogin: false,
    security: { roles: ['VendedorAcotado'], accesoTotal: false },
  };
}

async function mockShellApi(
  page: import('@playwright/test').Page,
  options: {
    session: ReturnType<typeof buildSession>;
    menu: unknown[];
  },
) {
  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: options.session,
      }),
    });
  });

  await page.route('**/api/v1/auth/me', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: options.session,
      }),
    });
  });

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'auth.logoutOk',
        resultado: {},
      }),
    });
  });

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: options.menu,
      }),
    });
  });

  await page.route('**/api/v1/users/me/preferences', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          locale: 'es',
          theme: 'generic.light',
          openInNewTab: false,
        },
      }),
    });
  });
}

async function loginAs(
  page: import('@playwright/test').Page,
  codigo: string,
  password: string,
) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill(codigo);
  await page.locator('input[name="password"]').fill(password);
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('vendedor acotado ve subconjunto y no ve stock', async ({ page }) => {
  await mockShellApi(page, {
    session: buildSession(21, 'vendedor.acotado.mvp', 'Vendedor Acotado MVP'),
    menu: acotadoMenu,
  });

  await loginAs(page, 'vendedor.acotado.mvp', 'secret');

  await expect(page.getByTestId('menuSidebarItem-pedidosIngresados')).toBeVisible();
  await expect(page.getByTestId('menuSidebarItem-stock')).toHaveCount(0);
});

test('supervisor ve item excluido del acotado', async ({ page }) => {
  await mockShellApi(page, {
    session: buildSession(22, 'supervisor.mvp', 'Supervisor MVP'),
    menu: supervisorMenu,
  });

  await loginAs(page, 'supervisor.mvp', 'secret');

  await expect(page.getByTestId('menuSidebarItem-pedidosIngresados')).toBeVisible();
  await expect(page.getByTestId('menuSidebarItem-stock')).toBeVisible();
});

test('usuario sin menu muestra estado vacio', async ({ page }) => {
  await mockShellApi(page, {
    session: buildSession(23, 'vendedor.sinMenu.mvp', 'Vendedor Sin Menu MVP'),
    menu: [],
  });

  await loginAs(page, 'vendedor.sinMenu.mvp', 'secret');

  await expect(page.getByTestId('menuSidebarEmptyState')).toBeVisible();
});

test('vista operationalOnly oculta agrupadores', async ({ page }) => {
  await mockShellApi(page, {
    session: buildSession(21, 'vendedor.acotado.mvp', 'Vendedor Acotado MVP'),
    menu: hierarchicalMenu,
  });

  await loginAs(page, 'vendedor.acotado.mvp', 'secret');

  await expect(page.getByTestId('menuSidebarItem-grupoConsultas')).toBeVisible();
  await page.getByTestId('menuToggleDisplayMode').click();
  await expect(page.getByTestId('menuSidebarItem-grupoConsultas')).toHaveCount(0);
  await expect(page.getByTestId('menuSidebarItem-pedidosIngresados')).toBeVisible();
});

test('controles de menu se cargan por usuario tras cambio de sesion', async ({ page }) => {
  const userA = buildSession(1, 'usuario.a', 'Usuario A');
  const userB = buildSession(2, 'usuario.b', 'Usuario B');

  await page.route('**/api/v1/auth/login', async (route) => {
    const payload = route.request().postDataJSON() as { codigo?: string };
    const session = payload.codigo === 'usuario.a' ? userA : userB;

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: session }),
    });
  });

  await page.route('**/api/v1/auth/me', async (route) => {
    const authHeader = route.request().headers()['authorization'] ?? '';
    const session = authHeader.includes('token-1') ? userA : userB;

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: session }),
    });
  });

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'auth.logoutOk', resultado: {} }),
    });
  });

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: acotadoMenu }),
    });
  });

  await page.route('**/api/v1/users/me/preferences', async (route) => {
    await route.fulfill({
      status: 404,
      contentType: 'application/json',
      body: JSON.stringify({ error: 404, respuesta: 'not.found', resultado: {} }),
    });
  });

  await loginAs(page, 'usuario.a', 'secret');
  await page.getByTestId('menuToggleDisplayMode').click();
  await expect(page.getByTestId('menuToggleDisplayMode')).toHaveAttribute('aria-pressed', 'true');

  await clickAvatarMenuItem(page, 'avatarMenuItemLogout');
  await expect(page).toHaveURL(/\/login$/);

  await loginAs(page, 'usuario.b', 'secret');
  await expect(page.getByTestId('menuToggleDisplayMode')).toHaveAttribute('aria-pressed', 'false');
});

test('navegacion desde sidebar resalta ruta activa', async ({ page }) => {
  await mockShellApi(page, {
    session: buildSession(21, 'vendedor.acotado.mvp', 'Vendedor Acotado MVP'),
    menu: acotadoMenu,
  });

  await loginAs(page, 'vendedor.acotado.mvp', 'secret');
  await page.getByTestId('menuSidebarItem-pedidosIngresados').click();

  await expect(page).toHaveURL(/\/pedidos\/ingresados$/);
  await expect(page.getByTestId('process-active-route')).toContainText('/pedidos/ingresados');
});

test('login no muestra sidebar de procesos', async ({ page }) => {
  await page.goto('/login');
  await expect(page.getByTestId('login-form')).toBeVisible();
  await expect(page.getByTestId('shellSidebar')).toHaveCount(0);
});
