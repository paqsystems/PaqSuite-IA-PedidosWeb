import { useCallback } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ComprobanteListadoMobileView } from '../../consultas/components/ComprobanteListadoMobileView';
import { fetchPedidosPendientes } from '../../consultas/api/consultaApi';
import { PedidosPendientesWebView } from './PedidosPendientesWebView';

export function PedidosPendientesPage() {
  if (isNativeApp()) {
    return <PedidosPendientesMobileView />;
  }

  return <PedidosPendientesWebView />;
}

function PedidosPendientesMobileView() {
  const loadData = useCallback(() => fetchPedidosPendientes(), []);

  return (
    <ComprobanteListadoMobileView
      pageTestId="page-pedidos-pendientes-mobile"
      pageTitleKey="pages.pedidosPendientes"
      listTestId="pedidosPendientesKardexList"
      loadData={loadData}
    />
  );
}
