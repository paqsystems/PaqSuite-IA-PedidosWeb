import { test, expect } from '@playwright/test';

const sessionPayload = {
  token: 'test-token',
  user: { id: 1, displayName: 'Supervisor MVP', login: 'supervisor.mvp' },
  functionalProfile: 'supervisor',
  codCliente: null,
  codVendedor: null,
  locale: 'es-AR',
  theme: 'light',
  firstLogin: false,
  inactivityTimeoutMinutes: 10,
  security: { roles: ['Supervisor'], accesoTotal: true },
};

const rolesPayload = {
  items: [
    {
      id: 10,
      nombreRol: 'RolTest',
      descripcionRol: 'Desc',
      accesoTotal: false,
      enUso: false,
    },
  ],
};

async function mockAdminSecurityApi(page: import('@playwright/test').Page) {
  await page.route('**/api/v1/**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: {} }),
    });
  });

  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: sessionPayload }),
    });
  });

  await page.route('**/api/v1/auth/me', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: sessionPayload }),
    });
  });

  await page.route('**/api/v1/config/public', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          gridLayoutsEnabled: false,
          pivotsEnabled: false,
          pivotLayoutsEnabled: false,
          excelImportEnabled: false,
          securityAdminEnabled: true,
        },
      }),
    });
  });

  await page.route('**/api/v1/admin/roles', async (route) => {
    if (route.request().method() === 'GET') {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: rolesPayload }),
      });
      return;
    }

    await route.continue();
  });

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: [] }),
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

async function login(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('supervisor.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
}

test('roles admin muestra grilla con flag habilitado', async ({ page }) => {
  await mockAdminSecurityApi(page);
  await login(page);
  await page.goto('/admin/roles');

  await expect(page.getByTestId('roles.admin')).toBeVisible();
  await expect(page.getByTestId('roles.grid')).toBeVisible();
  await expect(page.getByTestId('abmAddRow')).toBeVisible();
});
