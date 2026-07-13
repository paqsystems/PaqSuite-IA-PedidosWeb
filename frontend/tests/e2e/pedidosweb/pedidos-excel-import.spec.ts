import { expect, test, type Page } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const excelFixturePath = path.join(__dirname, '../../fixtures/pedido-individual-min.xlsx');

const excelImportGuid = 'E2E-GUID-PEDIDO-INDIVIDUAL';

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

async function mockPedidosCargaExcelApi(page: Page) {
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
        resultado: { excelImportEnabled: true, gridLayoutsEnabled: false },
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

  await page.route('**/api/v1/clientes/*/cabecera-inicial', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          cabecera: {
            cod_cliente: 'CLI001',
            cod_vended: 'VEN001',
            vendedor_nombre: 'Vendedor Demo',
            cod_condvta: 1,
            cod_transpor: 'MVP',
            id_de: 1,
            direccion_entrega: 'Calle Demo 123',
            lista_precios: 1,
            lista_precios_descripcion: 'Lista Demo',
            moneda: 1,
            incluye_iva: false,
            bonif_1: 0,
            bonif_2: 0,
            bonif_3: 0,
            observaciones: '',
            cod_perfil: 'MVP',
            leyenda_1: 'Leyenda pie 1',
            leyenda_2: 'Leyenda pie 2',
          },
          catalogos: {
            condicionesVenta: [{ codigo: 1, descripcion: 'Contado' }],
            transportes: [{ codigo: 'MVP', descripcion: 'Transporte Demo' }],
            listasPrecios: [{ cod_lista: 1, descripcion: 'Lista Demo', moneda: 1, incluye_iva: false }],
            direccionesEntrega: [{ id_de: 1, direccion: 'Calle Demo 123', localidad: 'CABA', habitual: true }],
            perfiles: [{ cod_perfil: 'MVP', descripcion: 'Perfil MVP' }],
          },
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
        resultado: { items: [], total: 0 },
      }),
    });
  });

  await page.route('**/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          codigoProceso: 'PEDIDO_INDIVIDUAL',
          nombreProceso: 'Importacion pedido individual',
          generaPlantilla: true,
          permiteProcesamientoParcial: false,
          permiteSoloValidar: false,
          procedimientoHost: 'pw_cargapedidos',
        },
      }),
    });
  });
}

async function mockExcelImportSuccessFlow(page: Page) {
  const lotSummary = {
    guidImportacion: excelImportGuid,
    codigoProceso: 'PEDIDO_INDIVIDUAL',
    estadoImportacion: 'lista_para_procesar',
    cantidadFilasValidas: 1,
    cantidadFilasConError: 0,
    permiteProcesamientoParcial: false,
    permiteSoloValidar: false,
  };

  await page.route('**/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/archivo/hojas', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: { hojas: ['Hoja1'] } }),
    });
  });

  await page.route('**/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/lotes', async (route) => {
    if (route.request().method() !== 'POST') {
      await route.fallback();
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: lotSummary }),
    });
  });

  await page.route('**/api/v1/excel-import/lotes/*/filas/validas', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          total: 1,
          items: [
            {
              numeroFilaExcel: 2,
              estadoFila: 'procesada',
              datos: {
                cod_cliente: 'CLI001',
                cod_articulo: 'ART-001',
                cantidad: 2,
                precio: 100,
                porc_bonif: 0,
                porc_iva: 21,
                descripcion_articulo: 'Articulo demo importado',
              },
            },
          ],
        },
      }),
    });
  });

  await page.route('**/api/v1/excel-import/lotes/*/procesar', async (route) => {
    if (route.request().method() !== 'POST') {
      await route.fallback();
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          estadoImportacion: 'procesada',
          cantidadFilasProcesadas: 1,
          cantidadFilasOmitidas: 0,
        },
      }),
    });
  });

  await page.route(/\/api\/v1\/excel-import\/lotes\/[^/]+$/, async (route) => {
    if (route.request().method() !== 'GET') {
      await route.fallback();
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: { ...lotSummary, estadoImportacion: 'procesada' },
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

test.describe('Pedidos carga — importacion Excel', () => {
  test('toolbar excel visible en modo nuevo cuando epic habilitado', async ({ page }) => {
    await mockPedidosCargaExcelApi(page);
    await login(page);

    await page.goto('/pedidos/carga?modo=nuevo');

    await expect(page.getByTestId('pedidos-carga-excel-toolbar')).toBeVisible({ timeout: 15_000 });
    await expect(page.getByTestId('excelHostToolbar')).toBeVisible();
  });

  test('importacion excel completa hidrata cliente y renglones', async ({ page }) => {
    await mockPedidosCargaExcelApi(page);
    await mockExcelImportSuccessFlow(page);
    await login(page);

    await page.goto('/pedidos/carga?modo=nuevo');
    await expect(page.getByTestId('pedidos-carga-excel-toolbar')).toBeVisible({ timeout: 15_000 });

    await page.getByTestId('excelHostImport').getByRole('button').click();
    await expect(page.getByTestId('excelHostImportModal')).toBeAttached();

    const fileInput = page.getByTestId('excelFileUpload').locator('input[type="file"]');
    await fileInput.setInputFiles(excelFixturePath);

    await expect(page.getByTestId('excelSheetSelect')).toBeVisible({ timeout: 10_000 });
    await page.getByTestId('excelImportSubmit').getByRole('button').click();

    await expect(page.getByTestId('cliente-cargado')).toBeAttached({ timeout: 20_000 });
    await expect(page.getByRole('gridcell', { name: 'ART-001' })).toBeVisible({ timeout: 15_000 });
    await expect(page.getByTestId('excelHostImport').getByRole('button')).toBeDisabled();
  });
});
