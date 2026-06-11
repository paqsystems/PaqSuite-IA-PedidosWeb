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

const pivotMetadata = {
  consultaId: 'CONSULTA_PILOTO_PIVOT',
  versionDefinicion: 1,
  pivotHabilitado: true,
  admiteDrilldown: true,
  configuracionGeneral: { mostrarGrillaYPivot: true, vistaInicial: 'grilla' },
  pivotBase: {
    filas: ['codCliente'],
    columnas: [],
    valores: [{ campoId: 'cantidad', agregacion: 'sum' }],
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
      rolesPermitidos: ['fila', 'columna'],
    },
    {
      campoId: 'cantidad',
      dataField: 'cantidad',
      caption: 'Cantidad',
      tipoDato: 'number',
      rolCampo: 'metrica',
      rolesPermitidos: ['valor'],
      agregacionDefault: 'sum',
      agregacionesPermitidas: ['sum', 'avg', 'count'],
    },
  ],
  filtrosGenerales: [
    {
      filtroId: 'codCliente',
      dataField: 'codCliente',
      caption: 'Cliente',
      obligatorio: true,
      tipoControl: 'select',
    },
  ],
  restricciones: {
    maximoFilas: 10,
    maximoColumnas: 10,
    maximoMetricas: 15,
    maximoRegistrosBase: 5000,
    bloquearSiExcedeVolumen: true,
    requiereFiltroPrevio: false,
  },
  exportacion: {
    excelBasicoHabilitado: true,
    excelFormateadoHabilitado: true,
    incluirMetadatos: true,
    incluirFiltrosAplicados: true,
  },
  persistencia: {},
};

async function mockPivotExportApi(page: import('@playwright/test').Page) {
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

  await page.route('**/api/v1/consultas/historial-ventas**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              codCliente: 'CLIMVP001',
              razonSocial: 'Cliente E2E',
              cantidad: 10,
              tipo: 'FV',
              numero: '1001',
            },
          ],
          metadata: { fecha_proceso: '2026-06-09T10:00:00Z', dias_ventas_detalladas: 30 },
        },
      }),
    });
  });

  await page.route('**/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/metadata', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: pivotMetadata }),
    });
  });

  await page.route('**/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/data', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            { codCliente: 'CLIMVP001', cantidad: 10 },
            { codCliente: 'CLIMVP001', cantidad: 5 },
          ],
          totalRegistros: 2,
          truncado: false,
        },
      }),
    });
  });
}

test('historial exporta pivot basico a excel', async ({ page }) => {
  test.setTimeout(60_000);

  await mockPivotExportApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await page.goto('/consultas/historial');

  await page.getByTestId('historialVentas.viewToggle').getByText('Pivot').click();
  await expect(page.getByTestId('historialVentas.pivotGrid')).toBeVisible();
  await expect(page.getByTestId('pivotExport')).toBeVisible();
  await expect(page.getByTestId('pivotExport')).toBeEnabled();

  const downloadPromise = page.waitForEvent('download');
  await page.getByTestId('pivotExport').click();
  await page.getByTestId('pivotExportBasic').click();
  const download = await downloadPromise;
  expect(download.suggestedFilename()).toMatch(/^CONSULTA_PILOTO_PIVOT_\d{8}_\d{4}\.xlsx$/);
});

test('pivot vacio deshabilita exportar', async ({ page }) => {
  await mockPivotExportApi(page);

  await page.route('**/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/data', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [],
          totalRegistros: 0,
          truncado: false,
        },
      }),
    });
  });

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await page.goto('/consultas/historial');

  await page.getByTestId('historialVentas.viewToggle').getByText('Pivot').click();
  await expect(page.getByTestId('pivotExport')).toHaveAttribute('aria-disabled', 'true');
});
