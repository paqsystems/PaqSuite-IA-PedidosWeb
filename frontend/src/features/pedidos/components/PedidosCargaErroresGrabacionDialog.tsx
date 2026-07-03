import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';

type PedidosCargaErroresGrabacionDialogProps = {
  visible: boolean;
  messages: string[];
  onClose: () => void;
  titleKey?: string;
  introKey?: string;
  testId?: string;
};

export function PedidosCargaErroresGrabacionDialog({
  visible,
  messages,
  onClose,
  titleKey = 'pedidos.carga.erroresGrabacionTitulo',
  introKey = 'pedidos.carga.erroresGrabacionIntro',
  testId = 'dialog-errores-grabacion',
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
      title={t(titleKey)}
      elementAttr={{ 'data-testid': testId }}
    >
      <div className="pedidosCargaErroresGrabacionDialog">
        <p className="pedidosCargaErroresGrabacionDialog__intro">
          {t(introKey)}
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
