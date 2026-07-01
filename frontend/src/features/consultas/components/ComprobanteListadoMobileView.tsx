import { useTranslation } from 'react-i18next';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
import type { ComprobanteConsultaRow, ConsultaResult } from '../api/consultaApi';
import { getComprobanteDetailFields, renderComprobanteCard } from './consultaMobileRenderers';

type ComprobanteListadoMobileViewProps = {
  pageTestId: string;
  pageTitleKey: string;
  listTestId: string;
  loadData: () => Promise<ConsultaResult<ComprobanteConsultaRow>>;
};

export function ComprobanteListadoMobileView({
  pageTestId,
  pageTitleKey,
  listTestId,
  loadData,
}: ComprobanteListadoMobileViewProps) {
  const { t } = useTranslation();

  return (
    <ConsultaKardexMobileView
      mode="client"
      pageTestId={pageTestId}
      pageTitleKey={pageTitleKey}
      listTestId={listTestId}
      keyExpr="id"
      loadData={loadData}
      detailTitle={(item) => item.numero || item.codPedido}
      detailFields={getComprobanteDetailFields()}
      renderCard={(item) => renderComprobanteCard(item, t)}
    />
  );
}
