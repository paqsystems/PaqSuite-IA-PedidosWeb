import { test, expect } from '@playwright/test';

const seedPassword = 'secret';

const clienteMenuFixture = [
  {
    id: 1,
    menuKey: 'dashboard',
    labelKey: 'menu.dashboard',
    text: 'Dashboard',
    routePath: '/dashboard',
    procedimiento: 'pw_dashboard',
    tipoProceso: 'P',
    order: 10,
    nodeType: 'process',
    children: [],
  },
];

type LoginMockOptions = {
  status: number;
  respuesta?: string;
  sessionPayload?: Record<string, unknown>;
};

async function mockLoginRoute(
  page: import('@playwright/test').Page,
  options: LoginMockOptions,
) {
  await page.route('**/api/v1/auth/login', async (route) => {
    if (options.status !== 200) {
      await route.fulfill({
        status: options.status,
        contentType: 'application/json',
        body: JSON.stringify({
          error: options.status,
          respuesta: options.respuesta ?? 'auth.invalidCredentials',
          resultado: {},
        }),
      });
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: options.sessionPayload,
      }),
    });
  });
}

async function mockPostLoginShell(
  page: import('@playwright/test').Page,
  menuItems: typeof clienteMenuFixture,
) {
  const sessionPayload = {
    token: 'token-perfil-ok',
    user: { id: 1, displayName: 'Cliente MVP', login: 'cliente.mvp' },
    functionalProfile: 'cliente',
    codCliente: 'CLIMVP001',
    codVendedor: null,
    locale: 'es-AR',
    theme: 'light',
    firstLogin: false,
    inactivityTimeoutMinutes: 10,
    security: { roles: ['Cliente'], accesoTotal: false },
  };

  await page.route('**/api/v1/auth/me', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: sessionPayload }),
    });
  });

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: menuItems }),
    });
  });

  await page.route('**/api/v1/users/me/preferences', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: { locale: 'es', theme: 'generic.light', openInNewTab: false },
      }),
    });
  });
}

async function submitLogin(page: import('@playwright/test').Page, codigo: string) {
  await page.goto('/login');
  await expect(page.getByTestId('login-form')).toBeVisible();
  await page.locator('input[name="codigo"]').fill(codigo);
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
}

test('login con cliente y vendedor mismo cod_login muestra error de perfil comercial', async ({
  page,
}) => {
  await mockLoginRoute(page, {
    status: 403,
    respuesta: 'auth.noCommercialProfile',
  });

  await submitLogin(page, 'usuario.perfilAmbiguo.mvp');

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByTestId('auth-error-no-commercial-profile')).toBeVisible();
  await expect(page.getByTestId('shellHeader')).toHaveCount(0);
});

test('login sin entidad comercial asignada muestra error de perfil comercial', async ({ page }) => {
  await mockLoginRoute(page, {
    status: 403,
    respuesta: 'auth.noCommercialProfile',
  });

  await submitLogin(page, 'usuario.sinVinculo.mvp');

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByTestId('auth-error-no-commercial-profile')).toBeVisible();
  await expect(page.getByTestId('shellHeader')).toHaveCount(0);
});

test('login con entidad asignada accede al shell y carga el menu', async ({ page }) => {
  await mockLoginRoute(page, {
    status: 200,
    sessionPayload: {
      token: 'token-cliente',
      user: { id: 1, displayName: 'Cliente MVP', login: 'cliente.mvp' },
      functionalProfile: 'cliente',
      codCliente: 'CLIMVP001',
      codVendedor: null,
      locale: 'es-AR',
      theme: 'light',
      firstLogin: false,
      inactivityTimeoutMinutes: 10,
      security: { roles: ['Cliente'], accesoTotal: false },
    },
  });
  await mockPostLoginShell(page, clienteMenuFixture);

  await submitLogin(page, 'cliente.mvp');

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByTestId('shellHeader')).toBeVisible();
  await expect(page.getByTestId('shellSidebar')).toBeVisible();
  await expect(page.getByTestId('menuSidebarEmptyState')).toHaveCount(0);
});
