import { test, expect } from '@playwright/test';

const sessionPayload = {
  token: 'test-token',
  user: {
    id: 1,
    displayName: 'Cliente MVP',
    login: 'cliente.mvp',
  },
  functionalProfile: 'cliente',
  codCliente: 'CLIMVP001',
  codVendedor: null,
  locale: 'es-AR',
  theme: 'light',
  firstLogin: false,
  security: {
    roles: ['Cliente'],
    accesoTotal: false,
  },
};

async function mockAuthenticatedApi(page: import('@playwright/test').Page) {
  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: sessionPayload,
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
        resultado: sessionPayload,
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
        resultado: [],
      }),
    });
  });

  await page.route('**/api/v1/users/me/preferences', async (route) => {
    await route.fulfill({
      status: 404,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 404,
        respuesta: 'not.found',
        resultado: {},
      }),
    });
  });
}

test('muestra formulario de login', async ({ page }) => {
  await page.goto('/login');
  await expect(page.getByTestId('login-form')).toBeVisible();
  await expect(page.getByTestId('login-submit')).toBeVisible();
});

test('login valido navega al shell con cuatro zonas', async ({ page }) => {
  await mockAuthenticatedApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByTestId('shellHeader')).toBeVisible();
  await expect(page.getByTestId('shellSidebar')).toBeVisible();
  await expect(page.getByTestId('shellMain')).toBeVisible();
  await expect(page.getByTestId('shellFooter')).toBeVisible();
  await expect(page.getByTestId('shell-footer-session')).toContainText('Cliente MVP');
  await expect(page.getByTestId('menuToggleSidebar')).toBeVisible();
  await expect(page.getByTestId('menuToggleExpandAll')).toBeVisible();
  await expect(page.getByTestId('menuToggleDisplayMode')).toBeVisible();
  await expect(page.getByTestId('shell-language-slot')).toContainText('Idioma: es');
});

test('navegacion interna mantiene el shell montado', async ({ page }) => {
  await mockAuthenticatedApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();

  await page.getByTestId('nav-pedidos-ingresados').click();

  await expect(page).toHaveURL(/\/pedidos\/ingresados$/);
  await expect(page.getByTestId('shellHeader')).toBeVisible();
  await expect(page.getByTestId('shellSidebar')).toBeVisible();
  await expect(page.getByTestId('shellFooter')).toBeVisible();
  await expect(page.getByTestId('process-active-route')).toContainText('/pedidos/ingresados');
});

test('sesion invalida redirige a login sin renderizar shell', async ({ page }) => {
  await page.addInitScript(() => {
    localStorage.setItem('pedidosweb.auth.token', 'expired-token');
    localStorage.setItem(
      'pedidosweb.auth.session',
      JSON.stringify({
        user: { id: 1, displayName: 'Cliente MVP', login: 'cliente.mvp' },
        functionalProfile: 'cliente',
        codCliente: null,
        codVendedor: null,
        locale: 'es-AR',
        theme: 'light',
        firstLogin: false,
        security: { roles: ['Cliente'], accesoTotal: false },
      }),
    );
  });

  await page.route('**/api/v1/auth/me', async (route) => {
    await route.fulfill({
      status: 401,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 401,
        respuesta: 'auth.unauthenticated',
        resultado: {},
      }),
    });
  });

  await page.goto('/dashboard');

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByTestId('login-form')).toBeVisible();
  await expect(page.getByTestId('shellHeader')).toHaveCount(0);
});

test('shell usable en viewport movil con toggle sidebar', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 667 });
  await mockAuthenticatedApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();

  await expect(page.getByTestId('shellHeader')).toBeVisible();
  await expect(page.getByTestId('shellMain')).toBeVisible();
  await expect(page.getByTestId('shellFooter')).toBeVisible();

  await page.getByTestId('menuToggleSidebar').click();
  await expect(page.getByTestId('shellSidebar')).toBeHidden();

  await page.getByTestId('menuToggleSidebar').click();
  await expect(page.getByTestId('shellSidebar')).toBeVisible();
  await expect(page.getByTestId('menuSidebarEmptyState')).toBeVisible();
});
