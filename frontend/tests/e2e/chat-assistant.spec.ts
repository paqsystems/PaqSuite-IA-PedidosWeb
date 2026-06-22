import { test, expect, type BrowserContext, type Page } from '@playwright/test';
import { clickAvatarMenuItem } from './helpers/avatarMenu';

const seedPassword = 'TestSeedPassword123';

const sessionPayload = {
  user: {
    id: 50,
    displayName: 'Chat Assistant MVP',
    login: 'chat.assistant.mvp',
  },
  functionalProfile: 'cliente',
  codCliente: 'CHATAS01',
  codVendedor: null,
  locale: 'es',
  theme: 'light',
  firstLogin: false,
  inactivityTimeoutMinutes: 10,
  security: {
    roles: ['Cliente'],
    accesoTotal: false,
  },
};

async function mockChatAssistantApi(context: BrowserContext) {
  await context.route('**/api/v1/**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: {} }),
    });
  });

  await context.route('**/api/v1/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          token: 'token-chat-assistant',
          ...sessionPayload,
        },
      }),
    });
  });

  await context.route('**/api/v1/auth/me', async (route) => {
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

  await context.route('**/api/v1/auth/logout', async (route) => {
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

  await context.route('**/api/v1/user/menu', async (route) => {
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

  await context.route('**/api/v1/users/me/preferences', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          locale: 'es',
          theme: 'generic.light',
          openInNewTab: false,
        },
      }),
    });
  });

  await context.route('**/api/v1/chat-assistant/providers', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              providerId: 'groq',
              displayName: 'Groq',
              supportsVision: false,
              requiresBaseUrl: false,
              supportUrl: 'https://console.groq.com/keys',
            },
          ],
        },
      }),
    });
  });

  await context.route('**/api/v1/chat-assistant/me/configuration', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          hasConfiguration: false,
          hasApiKey: false,
          apiKeyHint: '',
          providerId: '',
          modelId: '',
          baseUrl: '',
          supportsVision: false,
          isEnabled: false,
        },
      }),
    });
  });
}

async function loginAs(page: Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('chat.assistant.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

async function seedAuthStorageForNewTabs(context: BrowserContext, page: Page) {
  const storageEntries = await page.evaluate(() =>
    Object.fromEntries(Object.entries(localStorage)),
  );

  await context.addInitScript((entries) => {
    for (const [key, value] of Object.entries(entries)) {
      if (typeof value === 'string') {
        window.localStorage.setItem(key, value);
      }
    }
  }, storageEntries);
}

test('opens chat assistant in a new tab from avatar menu', async ({ page, context }) => {
  await mockChatAssistantApi(context);
  await loginAs(page);
  await seedAuthStorageForNewTabs(context, page);
  await expect(page.getByTestId('shellHeader')).toBeVisible();

  const popupPromise = context.waitForEvent('page');
  await clickAvatarMenuItem(page, 'avatarMenuItemChatAssistant');
  const chatPage = await popupPromise;
  await chatPage.waitForLoadState('networkidle');

  await expect(chatPage).toHaveURL(/\/chat-assistant$/);
  await expect(chatPage.getByTestId('chatAssistantPage')).toBeVisible();
  await expect(chatPage.getByTestId('chatAssistantEmptyState')).toBeVisible();
  await expect(page.getByTestId('shellHeader')).toBeVisible();
});
