import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import type { ImportacionMasivaSalidaAccion } from '../hooks/useImportacionMasivaNavigationGuard';

type ImportacionMasivaSalidaModalProps = {
  visible: boolean;
  onConfirm: (accion: ImportacionMasivaSalidaAccion) => void;
  onCancel: () => void;
};

export function ImportacionMasivaSalidaModal({
  visible,
  onConfirm,
  onCancel,
}: ImportacionMasivaSalidaModalProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={visible}
      onHiding={onCancel}
      showCloseButton
      title={t('pedidos.importacionMasiva.salidaTitulo')}
      width={520}
      height="auto"
      elementAttr={{ 'data-testid': 'importacionMasivaModalSalida' }}
    >
      <p>{t('pedidos.importacionMasiva.salidaMensaje')}</p>
      <div className="importacionMasivaPage__modalActions">
        <Button
          text={t('pedidos.importacionMasiva.salidaGrabarTodo')}
          type="default"
          onClick={() => onConfirm('grabarTodo')}
        />
        <Button
          text={t('pedidos.importacionMasiva.salidaCancelar')}
          stylingMode="outlined"
          onClick={() => onConfirm('cancelar')}
        />
        <Button
          text={t('pedidos.importacionMasiva.salidaRetornar')}
          stylingMode="text"
          onClick={() => onConfirm('retornar')}
        />
      </div>
    </Popup>
  );
}
