import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';

type PedidosCargaErroresGrabacionDialogProps = {
  visible: boolean;
  messages: string[];
  onClose: () => void;
};

export function PedidosCargaErroresGrabacionDialog({
  visible,
  messages,
  onClose,
}: PedidosCargaErroresGrabacionDialogProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      dragEnabled={false}
      showCloseButton={true}
      width={520}
      height="auto"
      title={t('pedidos.carga.erroresGrabacionTitulo')}
      elementAttr={{ 'data-testid': 'dialog-errores-grabacion' }}
    >
      <div className="pedidosCargaErroresGrabacionDialog">
        <p className="pedidosCargaErroresGrabacionDialog__intro">
          {t('pedidos.carga.erroresGrabacionIntro')}
        </p>
        <ul
          className="pedidosCargaErroresGrabacionDialog__list"
          data-testid="errores-grabacion-lista"
        >
          {messages.map((message, index) => (
            <li key={`${index}-${message}`}>{message}</li>
          ))}
        </ul>
        <div className="pedidosCargaErroresGrabacionDialog__actions">
          <Button
            text={t('pedidos.carga.erroresGrabacionCerrar')}
            type="default"
            onClick={onClose}
            elementAttr={{ 'data-testid': 'errores-grabacion-cerrar' }}
          />
        </div>
      </div>
    </Popup>
  );
}
