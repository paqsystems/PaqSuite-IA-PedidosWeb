import { apiRequest } from '../../../shared/http/client';

export type DashboardKpiMetric = {
  cantidad?: number;
  importe?: number;
  unidades?: number;
};

export type DashboardMesEnCursoEstado = {
  estado: number;
  cantidad: number;
  importe: number;
  unidades: number;
};

export type DashboardResumenMensual = {
  anio: number;
  mes: number;
  porEstado: DashboardMesEnCursoEstado[];
  fechaCalculo?: string;
};

export type DashboardOperativo = {
  moneda?: {
    simbolo?: string;
    codigo?: string;
  };
  presupuestosActivos?: DashboardKpiMetric;
  pedidosIngresados?: DashboardKpiMetric;
  pedidosPendientes?: DashboardKpiMetric;
  topClientePresupuestos?: {
    cod_client?: string;
    razon_social?: string;
    importe?: number;
  };
  topClientePedidosIngresados?: {
    cod_client?: string;
    razon_social?: string;
    importe?: number;
  };
  fechaCalculo?: string;
};

export async function fetchDashboardOperativo(): Promise<DashboardOperativo> {
  const response = await apiRequest<DashboardOperativo>('/dashboard/operativo');
  return response.resultado;
}

export async function fetchDashboardResumenMensual(): Promise<DashboardResumenMensual> {
  const response = await apiRequest<DashboardResumenMensual>('/dashboard/resumen-mensual');
  return response.resultado;
}
