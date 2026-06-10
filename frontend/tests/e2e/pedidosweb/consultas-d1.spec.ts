import { expect, test } from '@playwright/test';

test.describe.configure({ mode: 'serial' });

const menuFixture = [
  {
    id: 1,
    menuKey: 'grupoPedidos',
    labelKey: 'menu.grupoPedidos',
    text: 'Pedidos',
    routePath: null,
    procedimiento: 'grp_pedidos',
    tipoProceso: 'G',
    order: 10,
    nodeType: 'group',
    children: [
      {
        id: 2,
        menuKey: 'detallePedidos',
        labelKey: 'menu.detallePedidos',
        text: 'Detalle de pedidos',
        routePath: '/pedidos/detalle',
        procedimiento: 'pw_detallepedidos',
        tipoProceso: 'P',
        order: 15,
        nodeType: 'process',
        children: [],
      },
    ],
  },
  {
    id: 3,
    menuKey: 'grupoGeneral',
    labelKey: 'menu.grupoGeneral',
    text: 'General',
    routePath: null,
    procedimiento: 'grp_general',
    tipoProceso: 'G',
    order: 60,
    nodeType: 'group',
    children: [
      {
        id: 4,
        menuKey: 'consultaParametros',
        labelKey: 'menu.consultaParametros',
        text: 'Consulta de parámetros',
        routePath: '/general/parametros',
        procedimiento: 'pw_consultaparametros',
        tipoProceso: 'P',
        order: 61,
        nodeType: 'process',
        children: [],
      },
    ],
  },
];

const dashboardResultado = {
  moneda: { simbolo: '$', codigo: 'ARS' },
  presupuestosActivos: { cantidad: 0, importe: 0 },
  pedidosIngresados: { cantidad: 0, importe: 0 },
  pedidosPendientes: { cantidad: 0, importe: 0 },
  topClientePresupuestos: null,
  topClientePedidosIngresados: null,
};

async function mockConsultasD1Api(page: import('@playwright/test').Page) {
  await page.route('**/api/v1/**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: {} }),
    });
  });

  const sessionPayload = {
    token: 'token-consultas-d1',
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
        resultado: { gridLayoutsEnabled: false },
      }),
    });
  });

  await page.route('**/api/v1/dashboard/operativo', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: dashboardResultado,
      }),
    });
  });

  await page.route('**/api/v1/dashboard/resumen-mensual', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          anio: 2026,
          mes: 6,
          porEstado: [],
          fechaCalculo: '2026-06-04T10:00:00Z',
        },
      }),
    });
  });

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: null }),
    });
  });

  await page.route('**/api/v1/config/parametros**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              clave: 'MinutosWeb',
              caption: 'Minutos web',
              tooltip: 'Ventana de edición web',
              tipoValor: 'I',
              valorMostrado: '30',
            },
            {
              clave: 'DetallePorMail',
              caption: 'Detalle por mail',
              tooltip: '',
              tipoValor: 'B',
              valorMostrado: 'true',
            },
          ],
          programa: 'PedidosWeb',
          total: 2,
        },
      }),
    });
  });

  await page.route('**/api/v1/consultas/detalle-pedidos**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              codPedido: 'PED-E2E-001',
              codCliente: 'CLI001',
              razonSocial: 'Cliente Demo',
              estado: 0,
              numeroVisible: 42,
              total: 200,
              fecha: '2026-06-03T10:00:00Z',
              moneda: 1,
              codVended: 'VEN001',
              vendedorDescripcion: 'Vendedor Demo',
              listaPrecios: 1,
              listaPreciosDescripcion: 'Lista Demo',
              renglon: 1,
              codArticulo: 'ART-001',
              descripcionArticulo: 'Artículo demo',
              cantidad: 2,
              porcBonif: 5,
              precioLista: 100,
              precioNeto: 95,
              importeBruto: 190,
              importeNeto: 190,
              ivaNeto: 39.9,
              importeNetoConIva: 229.9,
            },
          ],
          page: 1,
          page_size: 20,
          total: 1,
          total_pages: 1,
          metadata: { fecha_proceso: '2026-06-03T12:00:00Z' },
        },
      }),
    });
  });
}

async function login(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('supervisor.mvp');
  await page.locator('input[name="password"]').fill('secret');
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('consulta parámetros: listado solo lectura por descripción', async ({ page }) => {
  test.setTimeout(60_000);
  await mockConsultasD1Api(page);
  await login(page);

  await page.getByTestId('menuSidebarItem-consultaParametros').click();
  await expect(page).toHaveURL(/\/general\/parametros$/);
  await expect(page.getByTestId('page-parametros-consulta')).toBeVisible();
  await expect(page.getByText('Minutos web')).toBeVisible({ timeout: 15_000 });
  await expect(page.getByText('MinutosWeb')).toHaveCount(0);
  await expect(page.getByText('30')).toBeVisible();
  await expect(page.getByRole('button', { name: /editar/i })).toHaveCount(0);
});

test('consulta detalle pedidos: renglón visible y estado como texto', async ({ page }) => {
  test.setTimeout(60_000);
  await mockConsultasD1Api(page);
  await login(page);

  await page.getByTestId('menuSidebarItem-detallePedidos').click();
  await expect(page).toHaveURL(/\/pedidos\/detalle$/);
  await expect(page.getByTestId('page-detalle-pedidos')).toBeVisible();
  await expect(page.getByText('ART-001')).toBeVisible({ timeout: 15_000 });
  await expect(page.getByText('Ingresado')).toBeVisible();
  await expect(page.getByText(/Fecha de proceso: \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}/)).toBeVisible();
  await expect(page.getByRole('gridcell', { name: /95[.,]00/ })).toBeVisible({ timeout: 15_000 });
  await expect(page.getByRole('button', { name: /editar/i })).toHaveCount(0);
  await expect(page.getByRole('button', { name: /eliminar/i })).toHaveCount(0);

  const exportButton = page.getByTestId('gridExportExcel');
  await expect(exportButton).toBeVisible({ timeout: 15_000 });
  await expect(exportButton).toBeEnabled();
});

test('consulta detalle pedidos: refresh recarga datos', async ({ page }) => {
  test.setTimeout(60_000);
  await mockConsultasD1Api(page);

  let requestCount = 0;
  await page.route('**/api/v1/consultas/detalle-pedidos**', async (route) => {
    requestCount += 1;
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              codPedido: 'PED-E2E-001',
              codCliente: 'CLI001',
              razonSocial: 'Cliente Demo',
              estado: 0,
              numeroVisible: 42,
              total: 200,
              fecha: '2026-06-03T10:00:00Z',
              moneda: 1,
              codVended: 'VEN001',
              vendedorDescripcion: 'Vendedor Demo',
              listaPrecios: 1,
              listaPreciosDescripcion: 'Lista Demo',
              renglon: 1,
              codArticulo: 'ART-001',
              descripcionArticulo: 'Artículo demo',
              cantidad: 2,
              porcBonif: 5,
              precioLista: 100,
              precioNeto: 95,
              importeBruto: 190,
              importeNeto: 190,
              ivaNeto: 39.9,
              importeNetoConIva: 229.9,
            },
          ],
          page: 1,
          page_size: 20,
          total: 1,
          total_pages: 1,
          metadata: { fecha_proceso: '2026-06-03T12:00:00Z' },
        },
      }),
    });
  });

  await login(page);
  await page.getByTestId('menuSidebarItem-detallePedidos').click();
  await expect(page.getByTestId('page-detalle-pedidos')).toBeVisible();
  await expect(page.getByText('ART-001')).toBeVisible({ timeout: 15_000 });
  expect(requestCount).toBeGreaterThanOrEqual(1);

  const refreshButton = page.getByTestId('gridRefresh');
  await expect(refreshButton).toBeVisible({ timeout: 15_000 });
  await refreshButton.click();
  await expect.poll(() => requestCount).toBeGreaterThanOrEqual(2);
});

test('consulta detalle pedidos: grilla vacía deshabilita export', async ({ page }) => {
  test.setTimeout(60_000);
  await mockConsultasD1Api(page);

  await page.route('**/api/v1/consultas/detalle-pedidos**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [],
          page: 1,
          page_size: 20,
          total: 0,
          total_pages: 0,
          metadata: { fecha_proceso: '2026-06-03T12:00:00Z' },
        },
      }),
    });
  });

  await login(page);
  await page.goto('/pedidos/detalle');
  await expect(page.getByTestId('page-detalle-pedidos')).toBeVisible();
  const exportButton = page.getByTestId('gridExportExcel');
  await expect(exportButton).toBeVisible({ timeout: 15_000 });
  await expect(exportButton).toHaveAttribute('aria-disabled', 'true');
});
