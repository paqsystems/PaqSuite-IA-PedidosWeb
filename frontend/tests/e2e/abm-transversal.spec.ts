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

async function loginAndOpenAbmDemo(page: import('@playwright/test').Page) {
  await mockAuthenticatedApi(page);
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await page.goto('/demo/abm');
  await expect(page.getByTestId('process-demo-abm')).toBeVisible();
}

test('demo ABM muestra grilla con boton + y acciones', async ({ page }) => {
  await loginAndOpenAbmDemo(page);

  await expect(page.getByTestId('dataGridDx-main')).toBeVisible();
  await expect(page.getByTestId('abmAddRow')).toBeVisible();
  await expect(page.getByTestId('abmEdit').first()).toBeVisible();
  await expect(page.getByTestId('abmDelete').first()).toBeVisible();
  await expect(page.getByTestId('dataGridDx-main').locator('.dx-datagrid-rowsview').getByText('DEMO-001')).toBeVisible();
});

test('alta edicion y baja en demo ABM', async ({ page }) => {
  await loginAndOpenAbmDemo(page);

  await page.getByTestId('abmAddRow').click();
  await expect(page.getByTestId('abmFormPopup')).toBeVisible();
  await page.getByTestId('abmFieldCode').fill('DEMO-E2E');
  await page.getByTestId('abmFieldName').fill('Registro E2E');
  await page.getByTestId('abmSave').click();
  await expect(page.getByTestId('abmFormPopup')).not.toBeVisible();
  await expect(page.getByTestId('dataGridDx-main').locator('.dx-datagrid-rowsview').getByText('DEMO-E2E')).toBeVisible();

  const editButton = page
    .getByTestId('dataGridDx-main')
    .locator('.dx-datagrid-rowsview')
    .getByText('DEMO-E2E')
    .locator('xpath=ancestor::tr')
    .getByTestId('abmEdit');
  await editButton.click();
  await expect(page.getByTestId('abmFormPopup')).toBeVisible();
  await page.getByTestId('abmFieldName').fill('Registro E2E editado');
  await page.getByTestId('abmSave').click();
  await expect(page.getByTestId('dataGridDx-main').locator('.dx-datagrid-rowsview').getByText('Registro E2E editado')).toBeVisible();

  const deleteButton = page
    .getByTestId('dataGridDx-main')
    .locator('.dx-datagrid-rowsview')
    .getByText('DEMO-E2E')
    .locator('xpath=ancestor::tr')
    .getByTestId('abmDelete');
  await deleteButton.click();
  await expect(page.getByTestId('abmConfirmDelete')).toBeVisible();
  await page.getByTestId('abmConfirmDelete').click();
  await expect(page.getByTestId('dataGridDx-main').locator('.dx-datagrid-rowsview').getByText('DEMO-E2E')).not.toBeVisible();
});
