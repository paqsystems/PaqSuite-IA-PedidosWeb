import { expect, test, type Page } from '@playwright/test';

const sessionPayload = {
  token: 'token-pedidosweb',
  user: { id: 10, displayName: 'Supervisor MVP', login: 'supervisor.mvp' },
  functionalProfile: 'supervisor',
  codCliente: null,
  codVendedor: 'VEN001',
  locale: 'es-AR',
  theme: 'light',
  firstLogin: false,
  inactivityTimeoutMinutes: 10,
  security: { roles: ['Supervisor'], accesoTotal: true },
};

const menuFixture = [
  {
    id: 3,
    menuKey: 'dashboard',
    labelKey: 'menu.dashboard',
    text: 'Dashboard',
    routePath: '/dashboard',
    procedimiento: 'pw_dashboard',
    tipoProceso: 'P',
    order: 40,
    nodeType: 'process',
    children: [],
  },
  {
    id: 2,
    menuKey: 'cargaPedidosPresupuestos',
    labelKey: 'menu.cargaPedidosPresupuestos',
    text: 'Carga de Pedidos',
    routePath: '/pedidos/carga',
    procedimiento: 'pw_cargapedidos',
    tipoProceso: 'P',
    order: 11,
    nodeType: 'process',
    children: [],
  },
];

async function mockCargaBaseApi(page: Page) {
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

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: menuFixture }),
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
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: { excelImportEnabled: false, gridLayoutsEnabled: false },
      }),
    });
  });

  await page.route('**/api/v1/config/parametros-carga', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          modificaPrecio: true,
          modificaBonArt: true,
          modificaBonCli: true,
          modificaListaPrec: true,
          modificaCondVta: true,
          modificaDirEntr: true,
          modificaExpreso: true,
          clienteLeyenda1: true,
          clienteLeyenda2: true,
          clienteLeyenda3: true,
          clienteLeyenda4: true,
          clienteLeyenda5: true,
          functionalProfile: 'supervisor',
          codMotivoCierreExitoso: 1,
          noEliminaPedido: false,
          noModificaPedido: false,
          cargaRecurrente: true,
        },
      }),
    });
  });

  await page.route('**/api/v1/clientes', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: [{ codCliente: 'CLI001', nombre: 'Cliente Demo', razonSocial: 'Cliente Demo SA' }],
      }),
    });
  });

  await page.route('**/api/v1/articulos**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: { items: [], total: 0 } }),
    });
  });
}

async function mockAsistenteTurnRemoveChoice(page: Page) {
  await page.route('**/api/v1/pedidos/carga/asistente/turn', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          replyText: 'pedidos.carga.asistente.elegirRenglon',
          actions: [
            {
              action: 'needsChoice',
              payload: {
                kind: 'renglonExistente',
                options: [
                  {
                    n: 1,
                    label: 'AB 3000 — ARROZ 30 KG · cant 10 · precio 100 · bonif 0%',
                    code: 'AB 3000',
                    renglon: 1,
                  },
                  {
                    n: 2,
                    label: 'AB 0501 — ARROZ 5 KG · cant 5 · precio 50 · bonif 0%',
                    code: 'AB 0501',
                    renglon: 2,
                  },
                ],
              },
              resultado: 'needsChoice',
            },
          ],
          pendingChoice: {
            kind: 'renglonExistente',
            operation: 'remove',
            options: [
              {
                n: 1,
                label: 'AB 3000 — ARROZ 30 KG · cant 10 · precio 100 · bonif 0%',
                code: 'AB 3000',
                renglon: 1,
              },
              {
                n: 2,
                label: 'AB 0501 — ARROZ 5 KG · cant 5 · precio 50 · bonif 0%',
                code: 'AB 0501',
                renglon: 2,
              },
            ],
          },
          configurationRequired: false,
        },
      }),
    });
  });
}

async function login(page: Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('supervisor.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test.describe('Pedidos carga — Asistente IA', () => {
  test('panel expandible y turno mock lista renglones ambiguos', async ({ page }) => {
    await mockCargaBaseApi(page);
    await mockAsistenteTurnRemoveChoice(page);
    await login(page);

    await page.goto('/pedidos/carga?modo=nuevo');
    await expect(page.getByTestId('cargaAsistenteIaPanel')).toBeVisible({ timeout: 15_000 });

    await page.getByTestId('cargaAsistenteIaToggle').click();
    const input = page.getByTestId('cargaAsistenteIaInput');
    await expect(input).toBeVisible({ timeout: 10_000 });
    await input.click();
    await input.pressSequentially('elimina el articulo arroz', { delay: 15 });
    await expect(page.getByTestId('cargaAsistenteIaSend')).toBeEnabled({ timeout: 5_000 });
    await page.getByTestId('cargaAsistenteIaSend').click();

    await expect(page.getByText(/Hay varios renglones/i)).toBeVisible({ timeout: 10_000 });
    await expect(page.getByText(/AB 3000/)).toBeVisible();
    await expect(page.getByText(/AB 0501/)).toBeVisible();
  });
});
