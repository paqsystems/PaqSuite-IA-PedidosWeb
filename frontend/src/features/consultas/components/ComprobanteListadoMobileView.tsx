import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
import type { DataGridRowAction } from '../../../shared/ui/grids';
import type { ComprobanteConsultaRow, ConsultaResult } from '../api/consultaApi';
import { ComprobanteCardMobileActions } from './ComprobanteCardMobileActions';
import { getComprobanteDetailFields, renderComprobanteCard } from './consultaMobileRenderers';

type ComprobanteListadoMobileViewProps = {
  pageTestId: string;
  pageTitleKey: string;
  listTestId: string;
  loadData: () => Promise<ConsultaResult<ComprobanteConsultaRow>>;
  rowActions?: DataGridRowAction<ComprobanteConsultaRow>[];
  refreshToken?: number;
};

export function ComprobanteListadoMobileView({
  pageTestId,
  pageTitleKey,
  listTestId,
  loadData,
  rowActions = [],
  refreshToken = 0,
}: ComprobanteListadoMobileViewProps) {
  const { t } = useTranslation();
  const enableCardActions = isNativeApp() && rowActions.length > 0;

  const stableLoadData = useCallback(() => loadData(), [loadData]);

  const renderCardActions = enableCardActions
    ? (item: ComprobanteConsultaRow) => (
        <ComprobanteCardMobileActions row={item} actions={rowActions} />
      )
    : undefined;

  return (
    <ConsultaKardexMobileView
      mode="client"
      pageTestId={pageTestId}
      pageTitleKey={pageTitleKey}
      listTestId={listTestId}
      keyExpr="id"
      loadData={stableLoadData}
      refreshToken={refreshToken}
      detailTitle={(item) => item.numero || item.codPedido}
      detailFields={getComprobanteDetailFields()}
      renderCard={(item) => renderComprobanteCard(item, t)}
      renderCardActions={renderCardActions}
    />
  );
}
