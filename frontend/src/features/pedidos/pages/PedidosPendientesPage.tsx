import { useCallback, useState } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ComprobanteListadoMobileView } from '../../consultas/components/ComprobanteListadoMobileView';
import { usePedidosPendientesMobileRowActions } from '../../consultas/hooks/useComprobanteMobileRowActions';
import { fetchPedidosPendientes } from '../../consultas/api/consultaApi';
import { PedidosPendientesWebView } from './PedidosPendientesWebView';

export function PedidosPendientesPage() {
  if (isNativeApp()) {
    return <PedidosPendientesMobileView />;
  }

  return <PedidosPendientesWebView />;
}

function PedidosPendientesMobileView() {
  const [refreshToken, setRefreshToken] = useState(0);
  const loadData = useCallback(() => fetchPedidosPendientes(), []);
  const rowActions = usePedidosPendientesMobileRowActions({
    onChanged: () => {
      setRefreshToken((value) => value + 1);
    },
  });

  return (
    <ComprobanteListadoMobileView
      pageTestId="page-pedidos-pendientes-mobile"
      pageTitleKey="pages.pedidosPendientes"
      listTestId="pedidosPendientesKardexList"
      loadData={loadData}
      rowActions={rowActions}
      refreshToken={refreshToken}
    />
  );
}
