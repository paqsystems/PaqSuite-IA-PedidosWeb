import { createPortal } from 'react-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import type { AbmModalMode } from './types/abmTypes';
import { abmTestIds } from './abmTestIds';
import './abmFormPopup.css';

type AbmFormPopupProps = {
  isOpen: boolean;
  mode: AbmModalMode;
  isSaving?: boolean;
  errorKey?: string | null;
  onClose: () => void;
  onSave: () => void;
  children: React.ReactNode;
};

function resolveTitleKey(mode: AbmModalMode): string {
  if (mode === 'create') {
    return 'abm.popup.titleCreate';
  }

  if (mode === 'view') {
    return 'abm.popup.titleView';
  }

  return 'abm.popup.titleEdit';
}

export function AbmFormPopup({
  isOpen,
  mode,
  isSaving = false,
  errorKey = null,
  onClose,
  onSave,
  children,
}: AbmFormPopupProps) {
  const { t } = useTranslation();
  const isReadOnly = mode === 'view';

  if (!isOpen) {
    return null;
  }

  return createPortal(
    <Popup
      visible={isOpen}
      onHiding={() => {
        if (!isSaving) {
          onClose();
        }
      }}
      showTitle
      showCloseButton
      dragEnabled={false}
      hideOnOutsideClick={false}
      width="min(560px, 96vw)"
      height="auto"
      maxHeight="90vh"
      shading
      title={t(resolveTitleKey(mode))}
      wrapperAttr={{ 'data-testid': abmTestIds.formPopup, class: 'abmFormPopup' }}
    >
      <div className="abmFormPopup__body">
        {children}
        {errorKey ? (
          <p className="abmFormPopup__error" data-testid="abmFormError">
            {t(errorKey)}
          </p>
        ) : null}
        <div className="abmFormPopup__actions">
          <Button
            text={t('abm.cancel')}
            stylingMode="outlined"
            disabled={isSaving}
            onClick={onClose}
            elementAttr={{ 'data-testid': 'abmCancel' }}
          />
          {!isReadOnly ? (
            <Button
              text={t('abm.save')}
              type="default"
              disabled={isSaving}
              onClick={onSave}
              elementAttr={{ 'data-testid': abmTestIds.save }}
            />
          ) : null}
        </div>
      </div>
    </Popup>,
    document.body,
  );
}
