import { useCallback, useState } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ComprobanteListadoMobileView } from '../../consultas/components/ComprobanteListadoMobileView';
import { usePedidosIngresadosMobileRowActions } from '../../consultas/hooks/useComprobanteMobileRowActions';
import { fetchPedidosIngresados } from '../../consultas/api/consultaApi';
import { PedidosIngresadosWebView } from './PedidosIngresadosWebView';

export function PedidosIngresadosPage() {
  if (isNativeApp()) {
    return <PedidosIngresadosMobileView />;
  }

  return <PedidosIngresadosWebView />;
}

function PedidosIngresadosMobileView() {
  const [refreshToken, setRefreshToken] = useState(0);
  const loadData = useCallback(() => fetchPedidosIngresados(), []);
  const rowActions = usePedidosIngresadosMobileRowActions({
    onChanged: () => {
      setRefreshToken((value) => value + 1);
    },
  });

  return (
    <ComprobanteListadoMobileView
      pageTestId="page-pedidos-ingresados-mobile"
      pageTitleKey="pages.pedidosIngresados"
      listTestId="pedidosIngresadosKardexList"
      loadData={loadData}
      rowActions={rowActions}
      refreshToken={refreshToken}
    />
  );
}
