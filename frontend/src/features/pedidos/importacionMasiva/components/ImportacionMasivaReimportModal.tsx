import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';

type ImportacionMasivaReimportModalProps = {
  visible: boolean;
  onReplace: () => void;
  onAppend: () => void;
  onCancel: () => void;
};

export function ImportacionMasivaReimportModal({
  visible,
  onReplace,
  onAppend,
  onCancel,
}: ImportacionMasivaReimportModalProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={visible}
      onHiding={onCancel}
      showCloseButton
      title={t('pedidos.importacionMasiva.reimportTitulo')}
      width={480}
      height="auto"
      elementAttr={{ 'data-testid': 'importacionMasivaModalReimport' }}
    >
      <p>{t('pedidos.importacionMasiva.reimportMensaje')}</p>
      <div className="importacionMasivaPage__modalActions">
        <Button text={t('pedidos.importacionMasiva.reimportReemplazar')} type="default" onClick={onReplace} />
        <Button text={t('pedidos.importacionMasiva.reimportAgregar')} stylingMode="outlined" onClick={onAppend} />
        <Button text={t('abm.cancel')} stylingMode="text" onClick={onCancel} />
      </div>
    </Popup>
  );
}
