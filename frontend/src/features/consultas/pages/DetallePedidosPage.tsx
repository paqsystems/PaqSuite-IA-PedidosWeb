import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { ConsultaGridPage } from '../components/ConsultaGridPage';
import { ComprobanteConsultaColumns } from '../components/ComprobanteConsultaColumns';
import { DetallePedidosConsultaColumns } from '../components/DetallePedidosConsultaColumns';
import { fetchDetallePedidos, type DetallePedidoConsultaRow } from '../api/consultaApi';

const proceso = 'pw_detallepedidos';
const gridId = 'pw_detallepedidos';

export function DetallePedidosPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchDetallePedidos(), []);

  return (
    <ConsultaGridPage<DetallePedidoConsultaRow>
      pageTestId="page-detalle-pedidos"
      pageTitleKey="pages.consultaDetallePedidos"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={[]}
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
