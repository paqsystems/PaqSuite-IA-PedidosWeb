import { lazy, Suspense, type ReactElement } from 'react';
import { mvpMenuRoutePaths } from '../features/menu/mvpMenuRoutes';

const PedidosCargaPage = lazy(() =>
  import('../features/pedidos/pages/PedidosCargaPage').then((module) => ({
    default: module.PedidosCargaPage,
  })),
);
const ImportacionMasivaPage = lazy(() =>
  import('../features/pedidos/importacionMasiva/pages/ImportacionMasivaPage').then((module) => ({
    default: module.ImportacionMasivaPage,
  })),
);
const PedidosIngresadosPage = lazy(() =>
  import('../features/pedidos/pages/PedidosIngresadosPage').then((module) => ({
    default: module.PedidosIngresadosPage,
  })),
);
const PedidosPendientesPage = lazy(() =>
  import('../features/pedidos/pages/PedidosPendientesPage').then((module) => ({
    default: module.PedidosPendientesPage,
  })),
);
const PresupuestosPage = lazy(() =>
  import('../features/presupuestos/pages/PresupuestosPage').then((module) => ({
    default: module.PresupuestosPage,
  })),
);
const DeudaPage = lazy(() =>
  import('../features/consultas/pages/DeudaPage').then((module) => ({
    default: module.DeudaPage,
  })),
);
const ChequesPage = lazy(() =>
  import('../features/consultas/pages/ChequesPage').then((module) => ({
    default: module.ChequesPage,
  })),
);
const HistorialVentasPage = lazy(() =>
  import('../features/consultas/pages/HistorialVentasPage').then((module) => ({
    default: module.HistorialVentasPage,
  })),
);
const StockPage = lazy(() =>
  import('../features/consultas/pages/StockPage').then((module) => ({
    default: module.StockPage,
  })),
);
const TratativasPage = lazy(() =>
  import('../features/presupuestos/pages/TratativasPage').then((module) => ({
    default: module.TratativasPage,
  })),
);
const DashboardPage = lazy(() =>
  import('../features/shell/pages/DashboardPage').then((module) => ({
    default: module.DashboardPage,
  })),
);
const IntegracionLogsPage = lazy(() =>
  import('../features/integracion/pages/IntegracionLogsPage').then((module) => ({
    default: module.IntegracionLogsPage,
  })),
);
const ParametrosConsultaPage = lazy(() =>
  import('../features/config/pages/ParametrosConsultaPage').then((module) => ({
    default: module.ParametrosConsultaPage,
  })),
);
const DetallePedidosPage = lazy(() =>
  import('../features/consultas/pages/DetallePedidosPage').then((module) => ({
    default: module.DetallePedidosPage,
  })),
);
const ExcelImportHistoryPage = lazy(() =>
  import('../features/excelImport/pages/ExcelImportHistoryPage').then((module) => ({
    default: module.ExcelImportHistoryPage,
  })),
);
const ExcelImportProcessPage = lazy(() =>
  import('../features/excelImport/pages/ExcelImportProcessPage').then((module) => ({
    default: module.ExcelImportProcessPage,
  })),
);
const ExcelStagingGridPage = lazy(() =>
  import('../features/excelImport/pages/ExcelStagingGridPage').then((module) => ({
    default: module.ExcelStagingGridPage,
  })),
);

export type PedidosWebRoutePath = (typeof mvpMenuRoutePaths)[number];

export type PedidosWebRoute = {
  path: PedidosWebRoutePath;
  element: ReactElement;
};

function withSuspense(element: ReactElement, testId: string): ReactElement {
  return (
    <Suspense
      fallback={
        <section data-testid={testId} />
      }
    >
      {element}
    </Suspense>
  );
}

export const pedidosWebRoutes: PedidosWebRoute[] = [
  { path: '/pedidos/carga', element: withSuspense(<PedidosCargaPage />, 'page-loading-pedidos-carga') },
  {
    path: '/pedidos/importacion-masiva',
    element: withSuspense(<ImportacionMasivaPage />, 'page-loading-importacion-masiva'),
  },
  {
    path: '/presupuestos/ingresados',
    element: withSuspense(<PresupuestosPage />, 'page-loading-presupuestos-ingresados'),
  },
  {
    path: '/pedidos/ingresados',
    element: withSuspense(<PedidosIngresadosPage />, 'page-loading-pedidos-ingresados'),
  },
  {
    path: '/pedidos/pendientes',
    element: withSuspense(<PedidosPendientesPage />, 'page-loading-pedidos-pendientes'),
  },
  {
    path: '/pedidos/detalle',
    element: withSuspense(<DetallePedidosPage />, 'page-loading-detalle-pedidos'),
  },
  { path: '/consultas/deuda', element: withSuspense(<DeudaPage />, 'page-loading-consulta-deuda') },
  { path: '/consultas/cheques', element: withSuspense(<ChequesPage />, 'page-loading-consulta-cheques') },
  {
    path: '/consultas/historial',
    element: withSuspense(<HistorialVentasPage />, 'page-loading-consulta-historial'),
  },
  { path: '/consultas/stock', element: withSuspense(<StockPage />, 'page-loading-consulta-stock') },
  {
    path: '/presupuestos/tratativas',
    element: withSuspense(<TratativasPage />, 'page-loading-presupuestos-tratativas'),
  },
  { path: '/dashboard', element: withSuspense(<DashboardPage />, 'page-loading-dashboard') },
  { path: '/integracion/logs', element: withSuspense(<IntegracionLogsPage />, 'page-loading-integracion-logs') },
  {
    path: '/general/parametros',
    element: withSuspense(<ParametrosConsultaPage />, 'page-loading-parametros-consulta'),
  },
  {
    path: '/excel-import/historial',
    element: withSuspense(<ExcelImportHistoryPage />, 'page-loading-excel-import-historial'),
  },
  {
    path: '/excel-import/procesos/:codigoProceso',
    element: withSuspense(<ExcelImportProcessPage />, 'page-loading-excel-import-proceso'),
  },
  {
    path: '/excel-import/lotes/:guidImportacion',
    element: withSuspense(<ExcelStagingGridPage />, 'page-loading-excel-import-lote'),
  },
];
