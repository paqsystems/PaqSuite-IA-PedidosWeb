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
      {
        id: 4,
        menuKey: 'pedidosIngresados',
        labelKey: 'menu.pedidosIngresados',
        text: 'Pedidos ingresados',
        routePath: '/pedidos/ingresados',
        procedimiento: 'pw_pedidosingresados',
        tipoProceso: 'P',
        order: 12,
        nodeType: 'process',
        children: [],
      },
    ],
  },
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
];

const dashboardResultado = {
  moneda: { simbolo: '$', codigo: 'ARS' },
  presupuestosActivos: { cantidad: 1, importe: 10 },
  pedidosIngresados: { cantidad: 2, importe: 20 },
  pedidosPendientes: { cantidad: 3, importe: 30 },
  topClientePresupuestos: { cod_client: 'CLI001', razon_social: 'Cliente Demo', importe: 10 },
  topClientePedidosIngresados: { cod_client: 'CLI001', razon_social: 'Cliente Demo', importe: 20 },
};

async function mockPedidosWebApi(page: import('@playwright/test').Page) {
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

  await page.route('**/api/v1/clientes', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: [{ codCliente: 'CLI001', nombre: 'Cliente Demo' }],
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
          functionalProfile: 'supervisor',
          codMotivoCierreExitoso: 1,
          noEliminaPedido: false,
          noModificaPedido: false,
        },
      }),
    });
  });

  await page.route('**/api/v1/articulos**', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              codArticulo: 'ART-001',
              descripcion: 'Artículo demo',
              porcIva: 21,
              bonificacion: 0,
            },
          ],
        },
      }),
    });
  });

  await page.route('**/api/v1/comprobantes/grabar', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          cod_pedido: 'PED-E2E-001',
          nro_visible: 42,
          guidSufijo: 'E2E001',
          mailEnviado: false,
        },
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

  await page.route('**/api/v1/consultas/pedidos-ingresados**', async (route) => {
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
              total: 20,
              fecha: '2026-06-02T10:00:00Z',
              puedeEditar: true,
              puedeEliminar: true,
              puedeCopiar: true,
            },
          ],
          metadata: { fecha_proceso: '2026-06-02T12:00:00Z' },
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

test('smoke sección 9: login y navegación a carga', async ({ page }) => {
  await mockPedidosWebApi(page);
  await login(page);

  await page.getByTestId('menuSidebarItem-cargaPedidosPresupuestos').click();
  await expect(page).toHaveURL(/\/pedidos\/carga$/);
  await expect(page.getByTestId('page-pedidos-carga')).toBeVisible();
});

test('dashboard §9 paso 8: muestra los 8 KPIs', async ({ page }) => {
  await mockPedidosWebApi(page);
  await login(page);

  await expect(page.getByTestId('page-dashboard')).toBeVisible();
  await expect(page.getByTestId('dashboardKpiPresupuestosCantidad')).toContainText('1');
  await expect(page.getByTestId('dashboardKpiPresupuestosImporte')).toContainText('10.00');
  await expect(page.getByTestId('dashboardKpiPedidosIngresadosCantidad')).toContainText('2');
  await expect(page.getByTestId('dashboardKpiPedidosIngresadosImporte')).toContainText('20.00');
  await expect(page.getByTestId('dashboardKpiPedidosPendientesCantidad')).toContainText('3');
  await expect(page.getByTestId('dashboardKpiPedidosPendientesImporte')).toContainText('30.00');
  await expect(page.getByTestId('dashboardTopClientePresupuestos')).toContainText('Cliente Demo');
  await expect(page.getByTestId('dashboardTopClientePedidos')).toContainText('Cliente Demo');
});

test('carga: grabar pedido muestra confirmación y toast mail fallido', async ({ page }) => {
  test.setTimeout(60_000);
  await mockPedidosWebApi(page);
  await login(page);

  await page.getByTestId('menuSidebarItem-cargaPedidosPresupuestos').click();
  await expect(page.getByTestId('page-pedidos-carga')).toBeVisible();
  await expect(page.getByTestId('cliente-cargado')).toBeAttached({ timeout: 15_000 });
  await expect(page.getByTestId('btn-grabar-pedido')).toBeVisible({ timeout: 15_000 });

  await page.getByTestId('btn-grabar-pedido').getByRole('button').click();
  await expect(page.getByTestId('confirmacion-grabacion')).toContainText('42', { timeout: 15_000 });
  await expect(page.getByTestId('confirmacion-grabacion')).toContainText('E2E001');
  await expect(page.getByTestId('aviso-mail-envio-fallido')).toBeVisible();
});

test('consulta pedidos ingresados: comprobante visible y export habilitado', async ({ page }) => {
  test.setTimeout(60_000);
  await mockPedidosWebApi(page);
  await login(page);

  await page.getByTestId('menuSidebarItem-pedidosIngresados').click();
  await expect(page).toHaveURL(/\/pedidos\/ingresados$/);
  await expect(page.getByTestId('page-pedidos-ingresados')).toBeVisible();
  await expect(page.getByText('42')).toBeVisible({ timeout: 15_000 });

  const exportButton = page.getByTestId('gridExportExcel');
  await expect(exportButton).toBeVisible();
  await expect(exportButton).toBeEnabled();
});
