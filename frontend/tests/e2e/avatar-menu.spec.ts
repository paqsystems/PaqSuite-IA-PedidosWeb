import { test, expect } from '@playwright/test';
import { clickAvatarMenuItem, openAvatarMenu } from './helpers/avatarMenu';

const seedPassword = 'TestSeedPassword123';

const sessionPayload = {
  user: {
    id: 50,
    displayName: 'Open Tab False MVP',
    login: 'openTab.false.mvp',
  },
  functionalProfile: 'cliente',
  codCliente: 'OPENTABF01',
  codVendedor: null,
  locale: 'es',
  theme: 'light',
  firstLogin: false,
  security: {
    roles: ['Cliente'],
    accesoTotal: false,
  },
};

async function mockAvatarApi(
  page: import('@playwright/test').Page,
  options: { openInNewTab?: boolean } = {},
) {
  let openInNewTab = options.openInNewTab ?? false;

  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          token: 'token-avatar',
          ...sessionPayload,
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
        resultado: sessionPayload,
      }),
    });
  });

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
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
        resultado: [],
      }),
    });
  });

  await page.route('**/api/v1/users/me/preferences', async (route) => {
    if (route.request().method() === 'PATCH') {
      const payload = route.request().postDataJSON() as { openInNewTab?: boolean };
      openInNewTab = payload.openInNewTab ?? openInNewTab;

      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 0,
          respuesta: 'preferences.updated',
          resultado: { openInNewTab },
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
        resultado: {
          locale: 'es',
          theme: 'generic.light',
          openInNewTab,
        },
      }),
    });
  });
}

async function loginAs(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('openTab.false.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('abre y cierra el menu avatar', async ({ page }) => {
  await mockAvatarApi(page);
  await loginAs(page);

  await openAvatarMenu(page);
  await expect(page.getByTestId('avatarMenuItemAppearance')).toBeVisible();
  await expect(page.getByTestId('avatarMenuItemChangePassword')).toBeVisible();
  await expect(page.getByTestId('avatarMenuItemLogout')).toBeVisible();
  await expect(page.getByTestId('avatarMenuItemOpenInNewTab')).toBeVisible();
});

test('persiste toggle abrir en nueva pestana tras recargar', async ({ page }) => {
  await mockAvatarApi(page, { openInNewTab: false });
  await loginAs(page);

  await openAvatarMenu(page);
  await page.getByTestId('avatarMenuItemOpenInNewTab').locator('input[type="checkbox"]').check();

  await page.reload();
  await expect(page).toHaveURL(/\/dashboard$/);

  await openAvatarMenu(page);
  await expect(
    page.getByTestId('avatarMenuItemOpenInNewTab').locator('input[type="checkbox"]'),
  ).toBeChecked();
});

test('cierra sesion desde el menu avatar', async ({ page }) => {
  await mockAvatarApi(page);
  await loginAs(page);

  await clickAvatarMenuItem(page, 'avatarMenuItemLogout');
  await expect(page).toHaveURL(/\/login$/);
});

test('navega a apariencia desde el menu avatar', async ({ page }) => {
  await mockAvatarApi(page);
  await loginAs(page);

  await clickAvatarMenuItem(page, 'avatarMenuItemAppearance');
  await expect(page).toHaveURL(/\/appearance$/);
  await expect(page.getByTestId('appearanceStubPage')).toBeVisible();
});

test('navega a cambiar contraseña desde el menu avatar', async ({ page }) => {
  await mockAvatarApi(page);
  await loginAs(page);

  await clickAvatarMenuItem(page, 'avatarMenuItemChangePassword');
  await expect(page).toHaveURL(/\/change-password$/);
});
