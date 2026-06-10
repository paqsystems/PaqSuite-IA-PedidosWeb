import { test, expect } from '@playwright/test';
import { clickAvatarMenuItem, openAvatarMenu } from './helpers/avatarMenu';

const seedPassword = 'TestSeedPassword123';

const sessionPayload = {
  user: {
    id: 60,
    displayName: 'Theme Light MVP',
    login: 'theme.light.mvp',
  },
  functionalProfile: 'cliente',
  codCliente: 'THEMLGT01',
  codVendedor: null,
  locale: 'es',
  theme: 'generic.light',
  firstLogin: false,
  inactivityTimeoutMinutes: 10,
  security: {
    roles: ['Cliente'],
    accesoTotal: false,
  },
};

async function mockThemeApi(
  page: import('@playwright/test').Page,
  options: { theme?: string; locale?: string } = {},
) {
  let persistedTheme = options.theme ?? 'generic.light';
  const persistedLocale = options.locale ?? 'es';

  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          token: 'token-theme',
          ...sessionPayload,
          locale: persistedLocale === 'pt' ? 'pt-BR' : `${persistedLocale}-AR`,
          theme: persistedTheme,
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
          locale: persistedLocale === 'pt' ? 'pt-BR' : `${persistedLocale}-AR`,
          theme: persistedTheme,
        },
      }),
    });
  });

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: {} }),
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
    if (route.request().method() === 'PATCH') {
      await route.continue();
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          locale: persistedLocale,
          theme: persistedTheme,
          openInNewTab: false,
        },
      }),
    });
  });

  await page.route('**/api/v1/users/me/preferences/theme', async (route) => {
    const payload = route.request().postDataJSON() as { theme?: string };
    persistedTheme = payload.theme ?? persistedTheme;

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'preferences.themeUpdated',
        resultado: { theme: persistedTheme },
      }),
    });
  });

  await page.route('**/api/v1/users/me/preferences/locale', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'preferences.localeUpdated',
        resultado: { locale: persistedLocale },
      }),
    });
  });
}

async function loginAs(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('theme.light.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('abre selector de tema desde menu avatar', async ({ page }) => {
  await mockThemeApi(page);
  await loginAs(page);

  await clickAvatarMenuItem(page, 'avatarMenuItemAppearance');
  await expect(page.getByTestId('themeSelectorModal')).toBeVisible();
  await expect(page.getByTestId('themeOption-generic.light')).toBeVisible();
  await expect(page.getByTestId('themeOption-generic.dark')).toBeVisible();
});

test('aplicar previsualiza sin cerrar y confirmar persiste cerrando', async ({ page }) => {
  await mockThemeApi(page, { theme: 'generic.light', locale: 'pt' });
  await loginAs(page);

  await clickAvatarMenuItem(page, 'avatarMenuItemAppearance');
  await expect(page.getByTestId('themeCurrentValue')).toBeVisible();
  await expect(page.getByTestId('themeCurrentValue')).toContainText('Tema atual');
  await expect(page.getByTestId('themeCancelButton')).toBeVisible();
  await expect(page.getByTestId('themeApplyButton')).toContainText('Aplicar');
  await expect(page.getByTestId('themeConfirmButton')).toContainText('Confirmar');
  const deepOption = page.getByTestId('themeOption-material.blue.light');
  await expect(deepOption).toBeVisible();
  await deepOption.click();
  await page.getByTestId('themeApplyButton').click();

  await expect(page.locator('html')).toHaveAttribute('data-theme', 'material.blue.light');
  await expect(page.getByTestId('themeCurrentValue')).toBeVisible();
  await expect(page.getByTestId('themeCancelButton')).toBeVisible();
  await expect(page.getByTestId('themeApplyButton')).toBeVisible();
  await expect(page.getByTestId('themeConfirmButton')).toBeVisible();

  await page.getByTestId('themeConfirmButton').click();
  await expect(page.getByTestId('themeCurrentValue')).toHaveCount(0);

  await page.reload();
  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'material.blue.light');
});

test('usuario con tema invalido arranca en generic.light', async ({ page }) => {
  await mockThemeApi(page, { theme: 'generic.light' });

  await page.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          token: 'token-theme-invalid',
          ...sessionPayload,
          user: { ...sessionPayload.user, login: 'theme.invalid.mvp', displayName: 'Theme Invalid MVP' },
          theme: 'generic.light',
        },
      }),
    });
  });

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('theme.invalid.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'generic.light');
});
