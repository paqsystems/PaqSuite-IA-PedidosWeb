import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import List from 'devextreme-react/list';
import Popup from 'devextreme-react/popup';
import { useCurrentTheme } from '../hooks/useCurrentTheme';
import {
  isSupportedThemeKey,
  supportedThemeKeys,
  themeDisplayLabel,
  type SupportedThemeKey,
} from '../model/supportedThemes';
import './themeSelectorModal.css';

type ThemeSelectorModalProps = {
  isOpen: boolean;
  onClose: () => void;
};

export function ThemeSelectorModal({ isOpen, onClose }: ThemeSelectorModalProps) {
  const { t, i18n } = useTranslation();
  const { currentTheme, persistedTheme, previewTheme, revertThemePreview, confirmTheme, isSaving, saveErrorKey } = useCurrentTheme();
  const [selectedTheme, setSelectedTheme] = useState<SupportedThemeKey>(
    isSupportedThemeKey(persistedTheme) ? persistedTheme : 'generic.light',
  );

  useEffect(() => {
    if (isOpen) {
      setSelectedTheme(isSupportedThemeKey(persistedTheme) ? persistedTheme : 'generic.light');
    }
  }, [persistedTheme, isOpen]);

  const resolvedLanguage = i18n.resolvedLanguage ?? i18n.language;
  const themeItems = useMemo(
    () => supportedThemeKeys.map((themeKey) => ({
      value: themeKey,
      label: themeDisplayLabel(themeKey, resolvedLanguage),
    })),
    [resolvedLanguage],
  );

  if (!isOpen) {
    return null;
  }

  function handleCancel() {
    revertThemePreview();
    onClose();
  }

  function handleApply() {
    previewTheme(selectedTheme);
  }

  async function handleConfirm() {
    const didPersist = await confirmTheme(selectedTheme);

    if (didPersist) {
      onClose();
    }
  }

  return (
    <Popup
      visible={isOpen}
      onHiding={() => {
        if (!isSaving) {
          handleCancel();
        }
      }}
      showTitle
      showCloseButton
      dragEnabled={false}
      width={560}
      height={640}
      shading
      title={t('theme.selector.title')}
      wrapperAttr={{ 'data-testid': 'themeSelectorModal', class: 'themeSelectorPopup' }}
    >
      <div className="themeSelectorModal">
        <p className="themeSelectorCurrent" data-testid="themeCurrentValue">
          {t('theme.selector.current')}: {themeDisplayLabel(
            isSupportedThemeKey(currentTheme) ? currentTheme : 'generic.light',
            i18n.resolvedLanguage ?? i18n.language,
          )}
        </p>

        <List
          className="themeSelectorList"
          dataSource={themeItems}
          keyExpr="value"
          selectionMode="single"
          focusStateEnabled={false}
          activeStateEnabled={!isSaving}
          pageLoadMode="scrollBottom"
          height={420}
          selectedItemKeys={[selectedTheme]}
          onSelectionChanged={(event) => {
            const nextItem = event.addedItems[0] as { value: SupportedThemeKey } | undefined;

            if (nextItem !== undefined) {
              setSelectedTheme(nextItem.value);
            }
          }}
          itemRender={(item: { value: SupportedThemeKey; label: string }) => (
            <div className="themeSelectorItem" data-testid={`themeOption-${item.value}`}>
              <span>{item.label}</span>
            </div>
          )}
        />

        {saveErrorKey !== null && (
          <p className="themeSelectorError" data-testid="theme-save-error">
            {t(saveErrorKey)}
          </p>
        )}

        <div className="themeSelectorActions">
          <Button
            stylingMode="outlined"
            text={t('theme.selector.cancel')}
            disabled={isSaving}
            onClick={handleCancel}
            elementAttr={{ 'data-testid': 'themeCancelButton' }}
          />
          <Button
            stylingMode="outlined"
            text={t('theme.selector.apply')}
            disabled={isSaving}
            onClick={handleApply}
            elementAttr={{ 'data-testid': 'themeApplyButton' }}
          />
          <Button
            type="default"
            text={t('theme.selector.confirm')}
            disabled={isSaving}
            onClick={() => {
              void handleConfirm();
            }}
            elementAttr={{ 'data-testid': 'themeConfirmButton' }}
          />
        </div>
      </div>
    </Popup>
  );
}
