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

function buildPivotMetadata(consultaId: string, valueField: string, valueCaption: string) {
  return {
    consultaId,
    versionDefinicion: 1,
    pivotHabilitado: true,
    admiteDrilldown: false,
    configuracionGeneral: { mostrarGrillaYPivot: true, vistaInicial: 'grilla' },
    pivotBase: {
      filas: ['codCliente'],
      columnas: [],
      valores: [{ campoId: valueField, agregacion: 'sum' }],
      mostrarSubtotales: true,
      mostrarTotalesGenerales: true,
    },
    campos: [
      {
        campoId: 'codCliente',
        dataField: 'codCliente',
        caption: 'Cliente',
        tipoDato: 'string',
        rolCampo: 'dimension',
        rolesPermitidos: ['fila', 'columna', 'valor'],
        agregacionesPermitidas: ['count', 'min', 'max'],
      },
      {
        campoId: valueField,
        dataField: valueField,
        caption: valueCaption,
        tipoDato: 'number',
        rolCampo: 'metrica',
        rolesPermitidos: ['valor'],
        agregacionDefault: 'sum',
        agregacionesPermitidas: ['sum', 'avg', 'min', 'max', 'count'],
      },
    ],
    filtrosGenerales: [],
    restricciones: {
      maximoFilas: 10,
      maximoColumnas: 10,
      maximoMetricas: 15,
      maximoRegistrosBase: 5000,
      bloquearSiExcedeVolumen: true,
      requiereFiltroPrevio: false,
    },
    exportacion: {},
    persistencia: {},
  };
}

async function mockPivotInformeApi(
  page: import('@playwright/test').Page,
  options: {
    consultaPath: string;
    consultaId: string;
    testIdPrefix: string;
    pageTestId: string;
    gridProceso: string;
    valueField: string;
    valueCaption: string;
    listItems: Record<string, unknown>[];
  },
) {
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

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'auth.logoutOk', resultado: {} }),
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
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          gridLayoutsEnabled: false,
          pivotsEnabled: true,
          pivotLayoutsEnabled: false,
        },
      }),
    });
  });

  await page.route(`**${options.consultaPath}**`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: options.listItems,
          metadata: { fecha_proceso: '2026-06-09T10:00:00Z' },
        },
      }),
    });
  });

  const pivotMetadata = buildPivotMetadata(options.consultaId, options.valueField, options.valueCaption);

  await page.route(`**/api/v1/pivots/consultas/${options.consultaId}/metadata`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: pivotMetadata }),
    });
  });

  await page.route(`**/api/v1/pivots/consultas/${options.consultaId}/data`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: options.listItems,
          totalRegistros: options.listItems.length,
          truncado: false,
        },
      }),
    });
  });

  return options;
}

async function loginAndOpen(page: import('@playwright/test').Page, path: string) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await page.goto(path);
}

test('deuda alterna grilla a pivot', async ({ page }) => {
  const config = await mockPivotInformeApi(page, {
    consultaPath: '/api/v1/consultas/deuda',
    consultaId: 'CONSULTA_DEUDA',
    testIdPrefix: 'consultaDeuda',
    pageTestId: 'page-consulta-deuda',
    gridProceso: 'pw_deuda',
    valueField: 'saldo',
    valueCaption: 'Saldo',
    listItems: [{ id: '1', codCliente: 'CLIMVP001', razonSocial: 'Cliente E2E', saldo: 1000 }],
  });

  await loginAndOpen(page, '/consultas/deuda');
  await expect(page.getByTestId(config.pageTestId)).toBeVisible();
  await expect(page.getByTestId(`${config.testIdPrefix}.gridView`)).toBeVisible();
  await expect(page.getByTestId(`dataGridDx-${config.gridProceso}`)).toBeVisible();

  await page.getByTestId(`${config.testIdPrefix}.viewToggle`).getByText('Pivot').click();
  await expect(page.getByTestId(`${config.testIdPrefix}.pivotGrid`)).toBeVisible();
});

test('detalle pedidos alterna grilla a pivot', async ({ page }) => {
  const config = await mockPivotInformeApi(page, {
    consultaPath: '/api/v1/consultas/detalle-pedidos',
    consultaId: 'CONSULTA_DETALLE_PEDIDOS',
    testIdPrefix: 'detallePedidos',
    pageTestId: 'page-detalle-pedidos',
    gridProceso: 'pw_detallepedidos',
    valueField: 'cantidad',
    valueCaption: 'Cantidad',
    listItems: [
      {
        id: '1-1',
        codCliente: 'CLIMVP001',
        razonSocial: 'Cliente E2E',
        codArticulo: 'ART001',
        cantidad: 3,
      },
    ],
  });

  await loginAndOpen(page, '/pedidos/detalle');
  await expect(page.getByTestId(config.pageTestId)).toBeVisible();
  await expect(page.getByTestId(`${config.testIdPrefix}.gridView`)).toBeVisible();

  await page.getByTestId(`${config.testIdPrefix}.viewToggle`).getByText('Pivot').click();
  await expect(page.getByTestId(`${config.testIdPrefix}.pivotGrid`)).toBeVisible();
});
