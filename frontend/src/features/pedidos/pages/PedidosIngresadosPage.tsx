import { useCallback } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ComprobanteListadoMobileView } from '../../consultas/components/ComprobanteListadoMobileView';
import { fetchPedidosIngresados } from '../../consultas/api/consultaApi';
import { PedidosIngresadosWebView } from './PedidosIngresadosWebView';

export function PedidosIngresadosPage() {
  if (isNativeApp()) {
    return <PedidosIngresadosMobileView />;
  }

  return <PedidosIngresadosWebView />;
}

function PedidosIngresadosMobileView() {
  const loadData = useCallback(() => fetchPedidosIngresados(), []);

  return (
    <ComprobanteListadoMobileView
      pageTestId="page-pedidos-ingresados-mobile"
      pageTitleKey="pages.pedidosIngresados"
      listTestId="pedidosIngresadosKardexList"
      loadData={loadData}
    />
  );
}
