import { useLayoutEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import TextBox from 'devextreme-react/text-box';
import './pivotLayoutToolbar.css';

type PivotLayoutSaveAsDialogProps = {
  isOpen: boolean;
  errorKey: string | null;
  onClose: () => void;
  onConfirm: (layoutName: string) => void;
};

export function PivotLayoutSaveAsDialog({
  isOpen,
  errorKey,
  onClose,
  onConfirm,
}: PivotLayoutSaveAsDialogProps) {
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
      title={t('pivotLayout.saveAs.title')}
      wrapperAttr={{ 'data-testid': 'pivotLayoutSaveAsDialog' }}
    >
      <div className="pivotLayoutSaveAsDialog">
        <TextBox
          value={layoutName}
          valueChangeEvent="input"
          label={t('pivotLayout.saveAs.nameLabel')}
          stylingMode="outlined"
          inputAttr={{
            'data-testid': 'pivotLayoutSaveAsName',
            'aria-label': t('pivotLayout.saveAs.nameLabel'),
          }}
          onValueChanged={(event) => {
            setLayoutName(String(event.value ?? '').trim());
          }}
        />
        {errorKey ? (
          <p className="pivotLayoutSaveAsDialog__error" data-testid="pivotLayoutSaveAsError">
            {t(errorKey)}
          </p>
        ) : null}
        <div className="pivotLayoutSaveAsDialog__actions">
          <Button
            text={t('pivotLayout.saveAs.cancel')}
            stylingMode="outlined"
            onClick={onClose}
            elementAttr={{ 'data-testid': 'pivotLayoutSaveAsCancel' }}
          />
          <Button
            text={t('pivotLayout.saveAs.confirm')}
            type="default"
            disabled={layoutName.length === 0}
            onClick={() => onConfirm(layoutName)}
            elementAttr={{ 'data-testid': 'pivotLayoutSaveAsConfirm' }}
          />
        </div>
      </div>
    </Popup>
  );
}
