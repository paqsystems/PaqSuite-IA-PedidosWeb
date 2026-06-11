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
  exportacion: {},
  persistencia: { habilitarDiseños: true },
};

async function mockPivotLayoutApi(page: import('@playwright/test').Page) {
  let activeConfigId: number | null = null;
  let nextConfigId = 1;
  const savedConfigs: Record<
    number,
    { configId: number; nombre: string; configuracionJson: { fields: unknown[] } }
  > = {};

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
          pivotLayoutsEnabled: true,
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
          items: [{ codCliente: 'CLIMVP001', cantidad: 10, tipo: 'FV', numero: '1001' }],
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
          items: [{ codCliente: 'CLIMVP001', cantidad: 10 }],
          totalRegistros: 1,
          truncado: false,
        },
      }),
    });
  });

  await page.route('**/api/v1/pivot-configs?**', async (route) => {
    const items = Object.values(savedConfigs).map((config) => ({
      configId: config.configId,
      nombre: config.nombre,
      createdByUserId: 1,
      isOwner: true,
      updatedAt: '2026-06-11T12:00:00Z',
    }));

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: '', resultado: { items } }),
    });
  });

  await page.route('**/api/v1/pivot-configs/active?**', async (route) => {
    if (activeConfigId === null) {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 0,
          respuesta: '',
          resultado: {
            configId: null,
            nombre: null,
            configuracionJson: null,
            restoreMode: 'pivotBase',
          },
        }),
      });
      return;
    }

    const config = savedConfigs[activeConfigId];

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: '',
        resultado: {
          configId: config.configId,
          nombre: config.nombre,
          configuracionJson: config.configuracionJson,
          versionDefinicionConsulta: 1,
          restoreMode: 'saved',
        },
      }),
    });
  });

  await page.route('**/api/v1/pivot-configs/active', async (route) => {
    if (route.request().method() === 'PUT') {
      const body = route.request().postDataJSON() as { configId: number | null };
      activeConfigId = body.configId;
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ error: 0, respuesta: 'pivotLayout.activeUpdated', resultado: {} }),
      });
      return;
    }

    await route.continue();
  });

  await page.route('**/api/v1/pivot-configs', async (route) => {
    if (route.request().method() === 'POST') {
      const body = route.request().postDataJSON() as {
        nombre: string;
        configuracionJson: { fields: unknown[] };
      };
      const configId = nextConfigId;
      nextConfigId += 1;
      activeConfigId = configId;
      savedConfigs[configId] = {
        configId,
        nombre: body.nombre,
        configuracionJson: body.configuracionJson,
      };

      await route.fulfill({
        status: 201,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 0,
          respuesta: 'pivotLayout.created',
          resultado: {
            configId,
            nombre: body.nombre,
            configuracionJson: body.configuracionJson,
            versionDefinicionConsulta: 1,
            restoreMode: 'saved',
          },
        }),
      });
      return;
    }

    await route.continue();
  });
}

test('historial persiste diseño pivot guardar como', async ({ page }) => {
  await mockPivotLayoutApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cliente.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await page.goto('/consultas/historial');

  await page.getByTestId('historialVentas.viewToggle').getByText('Pivot').click();
  await expect(page.getByTestId('historialVentas.pivotGrid')).toBeVisible();
  await expect(page.getByTestId('pivotLayoutToolbar')).toBeVisible();

  await page.getByTestId('pivotLayoutSaveAs').click();
  await expect(page.getByTestId('pivotLayoutSaveAsDialog')).toBeVisible();

  const nameInput = page.getByTestId('pivotLayoutSaveAsName');
  await nameInput.fill('Mi vista pivot');
  await expect(page.getByTestId('pivotLayoutSaveAsConfirm')).toBeEnabled();
  await page.getByTestId('pivotLayoutSaveAsConfirm').click();
  await expect(page.getByTestId('pivotLayoutSaveAsDialog')).not.toBeVisible();

  await page.getByTestId('pivotLayoutSelect').click();
  await expect(page.getByRole('option', { name: /Mi vista pivot/ })).toBeVisible();
});
