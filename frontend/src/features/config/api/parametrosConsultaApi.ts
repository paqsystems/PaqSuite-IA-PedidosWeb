import { apiRequest } from '../../../shared/http/client';

export type ParametroConsultaRow = {
  id: string;
  clave: string;
  caption: string;
  tooltip: string;
  tipoValor: string;
  valorMostrado: string;
};

type ApiParametroItem = {
  clave?: string;
  caption?: string;
  tooltip?: string;
  tipoValor?: string;
  valorMostrado?: string;
};

type ParametrosConsultaPayload = {
  items?: ApiParametroItem[];
  programa?: string;
  total?: number;
};

function mapParametroItem(item: ApiParametroItem): ParametroConsultaRow {
  const clave = item.clave ?? '';

  return {
    id: clave,
    clave,
    caption: item.caption ?? clave,
    tooltip: item.tooltip ?? '',
    tipoValor: item.tipoValor ?? 'S',
    valorMostrado: item.valorMostrado ?? '',
  };
}

export async function fetchParametrosConsulta(programa = 'PedidosWeb'): Promise<{
  items: ParametroConsultaRow[];
  programa: string;
  total: number;
}> {
  const response = await apiRequest<ParametrosConsultaPayload>(
    `/config/parametros?programa=${encodeURIComponent(programa)}`,
  );
  const payload = response.resultado;
  const items = payload.items ?? [];

  return {
    items: items.map(mapParametroItem),
    programa: payload.programa ?? programa,
    total: payload.total ?? items.length,
  };
}
