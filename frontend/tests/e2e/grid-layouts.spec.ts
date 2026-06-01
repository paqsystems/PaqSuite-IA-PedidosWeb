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

let savedLayouts: Array<{ id: number; layoutName: string; createdByUserId: number; isOwner: boolean }> = [];
let activeLayout: { layoutId: number | null; layoutName: string | null; stateJson: unknown } = {
  layoutId: null,
  layoutName: null,
  stateJson: null,
};

async function mockAuthenticatedApiWithLayouts(page: import('@playwright/test').Page) {
  savedLayouts = [];
  activeLayout = { layoutId: null, layoutName: null, stateJson: null };

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

  await page.route('**/api/v1/config/public', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: { gridLayoutsEnabled: true } }),
    });
  });

  await page.route('**/api/v1/grid-layouts/active**', async (route) => {
    if (route.request().method() === 'PUT') {
      const body = route.request().postDataJSON() as { layoutId: number | null };
      activeLayout.layoutId = body.layoutId;
      const found = savedLayouts.find((item) => item.id === body.layoutId);
      activeLayout.layoutName = found?.layoutName ?? null;
      activeLayout.stateJson = found
        ? { columns: [{ dataField: 'name', visible: false }] }
        : null;
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ error: 0, respuesta: 'gridLayout.activeUpdated', resultado: {} }),
      });
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: activeLayout }),
    });
  });

  await page.route('**/api/v1/grid-layouts?**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: { items: savedLayouts } }),
    });
  });

  await page.route('**/api/v1/grid-layouts', async (route) => {
    if (route.request().method() !== 'POST') {
      await route.fallback();
      return;
    }

    const body = route.request().postDataJSON() as { layoutName: string };
    const created = {
      id: savedLayouts.length + 1,
      layoutName: body.layoutName,
      createdByUserId: 1,
      isOwner: true,
    };
    savedLayouts.push(created);
    activeLayout = {
      layoutId: created.id,
      layoutName: created.layoutName,
      stateJson: { columns: [{ dataField: 'name', visible: false }] },
    };
    await route.fulfill({
      status: 201,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'gridLayout.created',
        resultado: activeLayout,
      }),
    });
  });
}

async function loginFromMockedSession(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
}

test('toolbar de layouts visible en dashboard', async ({ page }) => {
  await mockAuthenticatedApiWithLayouts(page);
  await loginFromMockedSession(page);
  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByTestId('gridLayoutToolbar')).toBeVisible();
  await expect(page.getByTestId('gridLayoutSaveAs')).toBeVisible();
});

test('guardar como abre dialogo y crea layout', async ({ page }) => {
  await mockAuthenticatedApiWithLayouts(page);
  await loginFromMockedSession(page);

  await page.getByTestId('gridLayoutSaveAs').click();
  await expect(page.getByTestId('gridLayoutSaveAsDialog')).toBeVisible();
  await page.getByTestId('gridLayoutSaveAsName').fill('Mi vista E2E');
  await page.getByTestId('gridLayoutSaveAsConfirm').click();
  await expect(page.getByTestId('gridLayoutSaveAsDialog')).not.toBeVisible();
});
