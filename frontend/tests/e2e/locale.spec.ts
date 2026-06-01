import { test, expect } from '@playwright/test';
import { clickAvatarMenuItem } from './helpers/avatarMenu';

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

async function mockAuthenticatedApi(
  page: import('@playwright/test').Page,
  options: { locale?: string } = {},
) {
  let persistedLocale = options.locale ?? 'es';

  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          ...sessionPayload,
          locale: persistedLocale === 'it' ? 'it-IT' : `${persistedLocale}-AR`,
        },
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
        resultado: {
          ...sessionPayload,
          locale: persistedLocale === 'it' ? 'it-IT' : `${persistedLocale}-AR`,
        },
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
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          locale: persistedLocale,
          theme: 'generic.light',
          openInNewTab: false,
        },
      }),
    });
  });

  await page.route('**/api/v1/users/me/preferences/locale', async (route) => {
    const payload = route.request().postDataJSON() as { locale?: string };
    persistedLocale = payload.locale ?? persistedLocale;

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'preferences.localeUpdated',
        resultado: {
          locale: persistedLocale,
        },
      }),
    });
  });
}

async function selectLocale(
  page: import('@playwright/test').Page,
  selectorTestId: string,
  localeCode: string,
) {
  await page.getByTestId(selectorTestId).locator('.dx-dropdowneditor-button').click();
  await page.getByTestId(`localeOption-${localeCode}`).click();
}

test('selector en login muestra textos en italiano', async ({ page }) => {
  await page.goto('/login');
  await selectLocale(page, 'localeSelectorLogin', 'it');

  await expect(page.getByRole('heading', { name: 'PedidosWeb' })).toBeVisible();
  await expect(page.getByTestId('login-submit')).toHaveText('Accedi');
  await expect(page.getByText('Utente')).toBeVisible();
});

test('cambio de idioma en header persiste tras recargar', async ({ page }) => {
  await mockAuthenticatedApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await selectLocale(page, 'localeSelectorHeader', 'it');

  await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
  await expect(page.getByTestId('nav-pedidos-ingresados')).toHaveText('Vai agli ordini ricevuti');

  await page.reload();

  await expect(page.getByTestId('localeSelectorHeader').getByRole('combobox')).toHaveValue('Italiano');
  await expect(page.getByTestId('nav-pedidos-ingresados')).toHaveText('Vai agli ordini ricevuti');
});

test('idioma elegido en shell se refleja al volver a login', async ({ page }) => {
  await mockAuthenticatedApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await selectLocale(page, 'localeSelectorHeader', 'it');
  await expect(page.getByTestId('localeSelectorHeader').getByRole('combobox')).toHaveValue('Italiano');

  await clickAvatarMenuItem(page, 'avatarMenuItemLogout');
  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByTestId('localeSelectorLogin').getByRole('combobox')).toHaveValue('Italiano');
  await expect(page.getByTestId('login-submit')).toHaveText('Accedi');
});

test('grilla demo muestra caption en italiano', async ({ page }) => {
  await mockAuthenticatedApi(page, { locale: 'it' });

  await page.goto('/login');
  await selectLocale(page, 'localeSelectorLogin', 'it');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();

  await expect(page.getByTestId('dataGridDx-main')).toBeVisible();
  await expect(page.locator('.dx-datagrid-headers').getByText('Nome')).toBeVisible();
});
