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
  inactivityTimeoutMinutes: 10,
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

async function loginAndOpenDashboard(page: import('@playwright/test').Page) {
  await mockAuthenticatedApi(page);
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('dashboard muestra DataGridDx con datos y capacidades transversales', async ({ page }) => {
  await loginAndOpenDashboard(page);

  const grid = page.getByTestId('dataGridDx-main');
  await expect(grid).toBeVisible();
  await expect(grid.locator('.dx-datagrid-rowsview').getByText('Alpha')).toBeVisible();
  await expect(grid.locator('.dx-datagrid-rowsview').getByText('Beta')).toBeVisible();
  await expect(grid.locator('.dx-datagrid-group-panel')).toBeVisible();
  await expect(grid.locator('.dx-datagrid-pager')).toBeVisible();
  await expect(grid.locator('.dx-datagrid-filter-row')).toBeVisible();
  await expect(grid.getByTestId('dataGridRowAction-edit').first()).toBeVisible();
});

test('ordenar por columna nombre', async ({ page }) => {
  await loginAndOpenDashboard(page);

  const nameHeader = page.locator('.dx-datagrid-headers').getByText('Nombre');
  await nameHeader.click();
  await expect(page.getByTestId('dataGridDx-main')).toBeVisible();
});
