import { useTranslation } from 'react-i18next';
import Popup from 'devextreme-react/popup';
import './consultaDetailPopup.css';

export type ConsultaDetailField<TItem> = {
  labelKey: string;
  getValue: (item: TItem) => string;
  visible?: (item: TItem) => boolean;
};

type ConsultaDetailPopupProps<TItem> = {
  item: TItem | null;
  title: string;
  fields: ConsultaDetailField<TItem>[];
  onClose: () => void;
  testId?: string;
};

export function ConsultaDetailPopup<TItem>({
  item,
  title,
  fields,
  onClose,
  testId = 'consultaDetailPopup',
}: ConsultaDetailPopupProps<TItem>) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={item !== null}
      onHiding={onClose}
      showTitle
      title={title}
      width={360}
      height="auto"
      dragEnabled={false}
      hideOnOutsideClick
      elementAttr={{ 'data-testid': testId }}
    >
      {item && (
        <dl className="consultaDetailPopup">
          {fields
            .filter((field) => field.visible?.(item) ?? true)
            .map((field) => (
              <div key={field.labelKey}>
                <dt>{t(field.labelKey)}</dt>
                <dd>{field.getValue(item)}</dd>
              </div>
            ))}
        </dl>
      )}
    </Popup>
  );
}
