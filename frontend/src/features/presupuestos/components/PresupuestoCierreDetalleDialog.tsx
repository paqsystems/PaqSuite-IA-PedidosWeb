import { useTranslation } from 'react-i18next';
import Popup from 'devextreme-react/popup';
import type { PresupuestoConsultaRow } from '../../consultas/api/consultaApi';

type PresupuestoCierreDetalleDialogProps = {
  visible: boolean;
  presupuesto: PresupuestoConsultaRow | null;
  onClose: () => void;
};

export function PresupuestoCierreDetalleDialog({
  visible,
  presupuesto,
  onClose,
}: PresupuestoCierreDetalleDialogProps) {
  const { t } = useTranslation();
  const cierre = presupuesto?.cierre;

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      dragEnabled={false}
      showCloseButton={true}
      width={520}
      height={320}
      title={t('presupuestos.cierreDetalle.title')}
      elementAttr={{ 'data-testid': 'presupuestoCierreDetalleDialog' }}
    >
      <dl className="presupuestoCierreDetalle">
        <dt>{t('consultas.column.numero')}</dt>
        <dd>{presupuesto?.numero ?? ''}</dd>
        <dt>{t('presupuestos.cierreDetalle.tipo')}</dt>
        <dd>{cierre?.tipoCierre ?? t('consultas.fechaProcesoSinDato')}</dd>
        <dt>{t('presupuestos.cierreDetalle.motivo')}</dt>
        <dd>{cierre?.motivoDescripcion ?? t('consultas.fechaProcesoSinDato')}</dd>
        <dt>{t('presupuestos.cierreDetalle.fecha')}</dt>
        <dd>{cierre?.fechaCierre ?? t('consultas.fechaProcesoSinDato')}</dd>
        <dt>{t('presupuestos.cierreDetalle.pedidoGenerado')}</dt>
        <dd>{cierre?.codPedidoGenerado ?? t('consultas.fechaProcesoSinDato')}</dd>
        <dt>{t('presupuestos.cierreDetalle.observacion')}</dt>
        <dd>{cierre?.observacion ?? t('consultas.fechaProcesoSinDato')}</dd>
      </dl>
    </Popup>
  );
}
