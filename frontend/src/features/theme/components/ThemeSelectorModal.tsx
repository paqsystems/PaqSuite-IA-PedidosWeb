import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useCurrentTheme } from '../hooks/useCurrentTheme';
import {
  isSupportedThemeKey,
  supportedThemeKeys,
  themeNameKey,
  type SupportedThemeKey,
} from '../model/supportedThemes';
import './themeSelectorModal.css';

type ThemeSelectorModalProps = {
  isOpen: boolean;
  onClose: () => void;
};

export function ThemeSelectorModal({ isOpen, onClose }: ThemeSelectorModalProps) {
  const { t } = useTranslation();
  const { currentTheme, changeTheme, isSaving, saveErrorKey } = useCurrentTheme();
  const [selectedTheme, setSelectedTheme] = useState<SupportedThemeKey>(
    isSupportedThemeKey(currentTheme) ? currentTheme : 'generic.light',
  );

  useEffect(() => {
    if (isOpen) {
      setSelectedTheme(isSupportedThemeKey(currentTheme) ? currentTheme : 'generic.light');
    }
  }, [currentTheme, isOpen]);

  if (!isOpen) {
    return null;
  }

  async function handleApply() {
    await changeTheme(selectedTheme);
    onClose();
  }

  return (
    <div className="themeSelectorOverlay" role="presentation" onClick={onClose}>
      <div
        className="themeSelectorModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="themeSelectorTitle"
        data-testid="themeSelectorModal"
        onClick={(event) => {
          event.stopPropagation();
        }}
      >
        <h2 id="themeSelectorTitle">{t('theme.selector.title')}</h2>
        <p data-testid="themeCurrentValue">
          {t('theme.selector.current')}: {t(themeNameKey(
            isSupportedThemeKey(currentTheme) ? currentTheme : 'generic.light',
          ))}
        </p>

        <fieldset className="themeSelectorOptions">
          <legend className="themeSelectorLegend">{t('theme.selector.title')}</legend>
          {supportedThemeKeys.map((themeKey) => (
            <label key={themeKey} className="themeSelectorOption" data-testid={`themeOption-${themeKey}`}>
              <input
                type="radio"
                name="themeSelection"
                value={themeKey}
                checked={selectedTheme === themeKey}
                disabled={isSaving}
                onChange={() => {
                  setSelectedTheme(themeKey);
                }}
              />
              <span>{t(themeNameKey(themeKey))}</span>
            </label>
          ))}
        </fieldset>

        {saveErrorKey !== null && (
          <p className="themeSelectorError" data-testid="theme-save-error">
            {t(saveErrorKey)}
          </p>
        )}

        <div className="themeSelectorActions">
          <button
            type="button"
            className="themeSelectorButton"
            data-testid="themeApplyButton"
            disabled={isSaving}
            onClick={() => {
              void handleApply();
            }}
          >
            {t('theme.selector.apply')}
          </button>
          <button
            type="button"
            className="themeSelectorButton themeSelectorButtonSecondary"
            disabled={isSaving}
            onClick={onClose}
          >
            {t('theme.selector.cancel')}
          </button>
        </div>
      </div>
    </div>
  );
}
