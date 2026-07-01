import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
import { ConsultaInformePivotPage } from '../components/ConsultaInformePivotPage';
import { ComprobanteConsultaColumns } from '../components/ComprobanteConsultaColumns';
import { DetallePedidosConsultaColumns } from '../components/DetallePedidosConsultaColumns';
import { fetchDetallePedidos, type DetallePedidoConsultaRow } from '../api/consultaApi';
import {
  getDetallePedidoDetailFields,
  renderDetallePedidoCard,
} from '../components/consultaMobileRenderers';

const proceso = 'pw_detallepedidos';
const gridId = 'pw_detallepedidos';
const pivotConsultaId = 'CONSULTA_DETALLE_PEDIDOS';

export function DetallePedidosPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchDetallePedidos(), []);

  if (isNativeApp()) {
    return (
      <ConsultaKardexMobileView
        mode="client"
        pageTestId="page-detalle-pedidos-mobile"
        pageTitleKey="pages.consultaDetallePedidos"
        listTestId="detallePedidosKardexList"
        keyExpr="id"
        loadData={loadData}
        detailTitle={(item) => item.numero || item.codPedido}
        detailFields={getDetallePedidoDetailFields()}
        renderCard={renderDetallePedidoCard}
      />
    );
  }

  return (
    <ConsultaInformePivotPage<DetallePedidoConsultaRow>
      pageTestId="page-detalle-pedidos"
      pageTitleKey="pages.consultaDetallePedidos"
      proceso={proceso}
      gridId={gridId}
      pivotConsultaId={pivotConsultaId}
      testIdPrefix="detallePedidos"
      loadData={loadData}
      enableDrillDown
      columns={
        <ComprobanteConsultaColumns
          t={t}
          estadoVisible
          extraColumns={<DetallePedidosConsultaColumns t={t} />}
        />
      }
    />
  );
}
