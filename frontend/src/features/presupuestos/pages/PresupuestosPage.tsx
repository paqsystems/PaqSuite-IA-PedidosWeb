import { useCallback, useState } from 'react';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ComprobanteListadoMobileView } from '../../consultas/components/ComprobanteListadoMobileView';
import { usePresupuestosActivosMobileRowActions } from '../../consultas/hooks/useComprobanteMobileRowActions';
import { fetchPresupuestosActivos } from '../../consultas/api/consultaApi';
import { PresupuestosWebView } from './PresupuestosWebView';

export function PresupuestosPage() {
  if (isNativeApp()) {
    return <PresupuestosMobileView />;
  }

  return <PresupuestosWebView />;
}

function PresupuestosMobileView() {
  const [refreshToken, setRefreshToken] = useState(0);
  const loadData = useCallback(() => fetchPresupuestosActivos(), []);
  const rowActions = usePresupuestosActivosMobileRowActions({
    tipoOrigen: 'presupuesto',
    onChanged: () => {
      setRefreshToken((value) => value + 1);
    },
  });

  return (
    <ComprobanteListadoMobileView
      pageTestId="page-presupuestos-ingresados-mobile"
      pageTitleKey="pages.presupuestosIngresados"
      listTestId="presupuestosIngresadosKardexList"
      loadData={loadData}
      rowActions={rowActions}
      refreshToken={refreshToken}
    />
  );
}
