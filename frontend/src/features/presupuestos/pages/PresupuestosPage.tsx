import { useCallback } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ComprobanteListadoMobileView } from '../../consultas/components/ComprobanteListadoMobileView';
import { fetchPresupuestosActivos } from '../../consultas/api/consultaApi';
import { PresupuestosWebView } from './PresupuestosWebView';

export function PresupuestosPage() {
  if (isNativeApp()) {
    return <PresupuestosMobileView />;
  }

  return <PresupuestosWebView />;
}

function PresupuestosMobileView() {
  const loadData = useCallback(() => fetchPresupuestosActivos(), []);

  return (
    <ComprobanteListadoMobileView
      pageTestId="page-presupuestos-ingresados-mobile"
      pageTitleKey="pages.presupuestosIngresados"
      listTestId="presupuestosIngresadosKardexList"
      loadData={loadData}
    />
  );
}
