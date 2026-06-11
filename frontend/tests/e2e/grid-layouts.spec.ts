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

const historialItem = {
  codCliente: 'CLIMVP001',
  razonSocial: 'Cliente E2E',
  tipo: 'FV',
  numero: '1001',
  codArticulo: 'ART-E2E',
  descripcion: 'Articulo historial E2E',
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

  await page.route('**/api/v1/consultas/historial-ventas**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [historialItem],
          metadata: { fecha_proceso: '2026-06-09T10:00:00Z', dias_ventas_detalladas: 30 },
        },
      }),
    });
  });

  await page.route('**/api/v1/grid-layouts/active**', async (route) => {
    if (route.request().method() === 'PUT') {
      const body = route.request().postDataJSON() as { layoutId: number | null };
      activeLayout.layoutId = body.layoutId;
      const found = savedLayouts.find((item) => item.id === body.layoutId);
      activeLayout.layoutName = found?.layoutName ?? null;
      activeLayout.stateJson = found
        ? { columns: [{ dataField: 'codCliente', visible: false }] }
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
      stateJson: { columns: [{ dataField: 'codCliente', visible: false }] },
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

async function loginAndOpenHistorial(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await page.goto('/consultas/historial');
  await expect(page.getByTestId('page-consulta-historial')).toBeVisible();
  await expect(page.getByTestId('dataGridDx-pw_historialventas')).toBeVisible();
}

test('toolbar de layouts visible en consulta historial', async ({ page }) => {
  await mockAuthenticatedApiWithLayouts(page);
  await loginAndOpenHistorial(page);

  await expect(page.getByTestId('gridLayoutToolbar')).toBeVisible();
  await expect(page.getByTestId('gridLayoutSaveAs')).toBeVisible();
});

test('guardar como abre dialogo y crea layout', async ({ page }) => {
  await mockAuthenticatedApiWithLayouts(page);
  await loginAndOpenHistorial(page);

  await page.getByTestId('gridLayoutSaveAs').click();
  await expect(page.getByTestId('gridLayoutSaveAsDialog')).toBeVisible();
  await expect(page.getByTestId('gridLayoutSaveAsConfirm')).toBeDisabled();

  const nameInput = page.getByTestId('gridLayoutSaveAsName');
  await nameInput.fill('Mi vista E2E');
  await expect(page.getByTestId('gridLayoutSaveAsConfirm')).toBeEnabled();
  await page.getByTestId('gridLayoutSaveAsConfirm').click();
  await expect(page.getByTestId('gridLayoutSaveAsDialog')).not.toBeVisible();
});

test('layout propio muestra sufijo (*) en selector', async ({ page }) => {
  await mockAuthenticatedApiWithLayouts(page);
  savedLayouts = [{ id: 1, layoutName: 'Vista propia', createdByUserId: 1, isOwner: true }];
  activeLayout = {
    layoutId: 1,
    layoutName: 'Vista propia',
    stateJson: { columns: [{ dataField: 'codCliente', visible: false }] },
  };

  await loginAndOpenHistorial(page);

  await page.waitForResponse(
    (response) =>
      response.url().includes('/api/v1/grid-layouts/active') &&
      response.request().method() === 'GET' &&
      response.status() === 200,
  );

  const layoutSelect = page.getByTestId('gridLayoutSelect');
  await expect(layoutSelect).toHaveValue('Vista propia (*)', { timeout: 10000 });

  await layoutSelect.click();
  await expect(page.getByRole('option', { name: 'Vista propia (*)' })).toBeVisible();
});
