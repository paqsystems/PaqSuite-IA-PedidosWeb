import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';

type PedidosCargaConfirmacionDialogProps = {
  visible: boolean;
  message: string;
  onClose: () => void;
};

export function PedidosCargaConfirmacionDialog({
  visible,
  message,
  onClose,
}: PedidosCargaConfirmacionDialogProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      dragEnabled={false}
      showCloseButton={true}
      width={460}
      height="auto"
      title={t('pedidos.carga.confirmacionTitulo')}
      wrapperAttr={{ class: 'pedidosCargaDialogPopup' }}
      elementAttr={{ 'data-testid': 'dialog-confirmacion-grabar' }}
    >
      <div className="pedidosCargaConfirmacionDialog">
        <p className="pedidosCargaConfirmacionDialog__message" data-testid="confirmacion-grabacion">
          {message}
        </p>
        <div className="pedidosCargaConfirmacionDialog__actions">
          <Button
            text={t('pedidos.carga.confirmacionCerrar')}
            type="default"
            stylingMode="contained"
            width={120}
            onClick={onClose}
            elementAttr={{ 'data-testid': 'confirmacion-grabacion-cerrar' }}
          />
        </div>
      </div>
    </Popup>
  );
}
