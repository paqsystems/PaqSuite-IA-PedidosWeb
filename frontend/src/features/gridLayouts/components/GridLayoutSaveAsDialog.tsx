import { useLayoutEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import TextBox from 'devextreme-react/text-box';

type GridLayoutSaveAsDialogProps = {
  isOpen: boolean;
  errorKey: string | null;
  onClose: () => void;
  onConfirm: (layoutName: string) => void;
};

export function GridLayoutSaveAsDialog({
  isOpen,
  errorKey,
  onClose,
  onConfirm,
}: GridLayoutSaveAsDialogProps) {
  const { t } = useTranslation();
  const [layoutName, setLayoutName] = useState('');

  useLayoutEffect(() => {
    if (isOpen) {
      setLayoutName('');
    }
  }, [isOpen]);

  if (!isOpen) {
    return null;
  }

  return (
    <Popup
      visible={isOpen}
      onHiding={onClose}
      showTitle
      showCloseButton
      dragEnabled={false}
      width={420}
      height="auto"
      title={t('gridLayout.saveAs.title')}
      wrapperAttr={{ 'data-testid': 'gridLayoutSaveAsDialog' }}
    >
      <div className="gridLayoutSaveAsDialog">
        <TextBox
          value={layoutName}
          valueChangeEvent="input"
          label={t('gridLayout.saveAs.nameLabel')}
          stylingMode="outlined"
          inputAttr={{
            'data-testid': 'gridLayoutSaveAsName',
            'aria-label': t('gridLayout.saveAs.nameLabel'),
          }}
          onValueChanged={(event) => {
            setLayoutName(String(event.value ?? '').trim());
          }}
        />
        {errorKey ? (
          <p className="gridLayoutSaveAsDialog__error" data-testid="gridLayoutSaveAsError">
            {t(errorKey)}
          </p>
        ) : null}
        <div className="gridLayoutSaveAsDialog__actions">
          <Button
            text={t('gridLayout.saveAs.cancel')}
            stylingMode="outlined"
            onClick={onClose}
            elementAttr={{ 'data-testid': 'gridLayoutSaveAsCancel' }}
          />
          <Button
            text={t('gridLayout.saveAs.confirm')}
            type="default"
            disabled={layoutName.length === 0}
            onClick={() => onConfirm(layoutName)}
            elementAttr={{ 'data-testid': 'gridLayoutSaveAsConfirm' }}
          />
        </div>
      </div>
    </Popup>
  );
}
