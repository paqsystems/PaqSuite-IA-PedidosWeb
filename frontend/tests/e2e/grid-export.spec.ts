import { test, expect } from '@playwright/test';

const sessionPayload = {
  token: 'test-token',
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

async function mockAuthenticatedApi(page: import('@playwright/test').Page) {
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
  await mockAuthenticatedApi(page);
  await page.route('**/api/v1/config/public', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: { gridLayoutsEnabled: true } }),
    });
  });

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('dashboard muestra exportar habilitado con datos', async ({ page }) => {
  await login(page);

  const exportButton = page.getByTestId('gridExportExcel');
  await expect(exportButton).toBeVisible();
  await expect(exportButton).toBeEnabled();
});

test('exportar formateada descarga archivo sugerido', async ({ page }) => {
  test.setTimeout(60_000);

  await login(page);

  const downloadPromise = page.waitForEvent('download');
  await page.locator('[data-testid="gridExportExcel"] .dx-dropdownbutton-action').click();
  const download = await downloadPromise;
  expect(download.suggestedFilename()).toMatch(/^pw_dashboard_main_\d{8}_\d{4}\.xlsx$/);
});

test('grilla vacia deshabilita exportar', async ({ page }) => {
  await login(page);
  await page.goto('/demo/export-empty');
  await expect(page.getByTestId('process-export-empty')).toBeVisible();

  const exportButton = page.getByTestId('gridExportExcel');
  await expect(exportButton).toBeVisible();
  await expect(exportButton).toHaveAttribute('aria-disabled', 'true');
});
