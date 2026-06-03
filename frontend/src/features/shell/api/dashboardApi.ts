import { apiRequest } from '../../../shared/http/client';

export type DashboardOperativo = {
  moneda?: {
    simbolo?: string;
    codigo?: string;
  };
  presupuestosActivos?: {
    cantidad?: number;
    importe?: number;
  };
  pedidosIngresados?: {
    cantidad?: number;
    importe?: number;
  };
  pedidosPendientes?: {
    cantidad?: number;
    importe?: number;
  };
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
