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

async function mockPermisosApi(page: import('@playwright/test').Page) {
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

  await page.route('**/api/v1/admin/permisos', async (route) => {
    if (route.request().method() === 'GET') {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: { items: [] } }),
      });
      return;
    }

    await route.continue();
  });

  await page.route('**/api/v1/admin/roles', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: { items: [{ id: 2, nombreRol: 'Cliente', descripcionRol: '', accesoTotal: false, enUso: true }] },
      }),
    });
  });

  await page.route('**/api/v1/admin/usuarios**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [{ id: 5, codigo: 'vendedor.acotado.mvp', nameUser: 'Vendedor Acotado MVP' }],
          page: 1,
          page_size: 20,
          total: 1,
          total_pages: 1,
        },
      }),
    });
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

test('bulk por usuario valida ancla vacia', async ({ page }) => {
  await mockPermisosApi(page);
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('supervisor.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await page.goto('/admin/permisos');

  await expect(page.getByTestId('permisos.admin')).toBeVisible();
  await page.getByTestId('permisos.bulk.byUser').click();

  const dialog = page.getByRole('dialog', { name: 'Asignacion masiva por usuario' });
  await expect(dialog).toBeVisible();

  await dialog.getByRole('button', { name: 'Guardar' }).click();
  await expect(page.getByTestId('permisos.bulk.validation')).toContainText('Seleccione Usuario');
});
