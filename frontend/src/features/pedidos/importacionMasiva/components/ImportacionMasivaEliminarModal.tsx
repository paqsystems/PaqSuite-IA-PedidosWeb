import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';

type ImportacionMasivaEliminarModalProps = {
  visible: boolean;
  onConfirm: () => void;
  onCancel: () => void;
};

export function ImportacionMasivaEliminarModal({
  visible,
  onConfirm,
  onCancel,
}: ImportacionMasivaEliminarModalProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={visible}
      onHiding={onCancel}
      showCloseButton
      title={t('pedidos.importacionMasiva.eliminarTitulo')}
      width={420}
      height="auto"
      elementAttr={{ 'data-testid': 'importacionMasivaModalEliminar' }}
    >
      <p>{t('pedidos.importacionMasiva.eliminarMensaje')}</p>
      <div className="importacionMasivaPage__modalActions">
        <Button text={t('abm.action.delete')} type="danger" onClick={onConfirm} />
        <Button text={t('abm.cancel')} stylingMode="text" onClick={onCancel} />
      </div>
    </Popup>
  );
}
