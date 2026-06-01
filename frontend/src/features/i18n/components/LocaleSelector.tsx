import type { CSSProperties } from 'react';
import { useTranslation } from 'react-i18next';
import SelectBox from 'devextreme-react/select-box';
import { defaultLocale, supportedLocales, type SupportedLocale } from '../model/supportedLocales';
import './LocaleSelector.css';

type LocaleSelectorProps = {
  value: string;
  onChange: (locale: string) => void;
  testId: string;
  disabled?: boolean;
};

function localeFlagBackground(localeCode: SupportedLocale): string {
  switch (localeCode) {
    case 'es':
      return 'linear-gradient(to bottom, #75aadb 0 33%, #ffffff 33% 66%, #75aadb 66% 100%)';
    case 'en':
      return 'linear-gradient(#3c3b6e, #3c3b6e) left top / 45% 55% no-repeat, repeating-linear-gradient(to bottom, #b22234 0 7.7%, #ffffff 7.7% 15.4%)';
    case 'pt':
      return 'linear-gradient(90deg, #009c3b 0 40%, #ffdf00 40% 100%)';
    case 'fr':
      return 'linear-gradient(90deg, #0055a4 0 33%, #ffffff 33% 66%, #ef4135 66% 100%)';
    case 'it':
      return 'linear-gradient(90deg, #009246 0 33%, #ffffff 33% 66%, #ce2b37 66% 100%)';
    default:
      return 'none';
  }
}

export function LocaleSelector({ value, onChange, testId, disabled = false }: LocaleSelectorProps) {
  const { t } = useTranslation();
  const locales = supportedLocales.map((localeCode) => ({
    code: localeCode,
    label: t(`locale.name.${localeCode}`),
    flagClass: `flag-${localeCode}`,
    testId: `localeOption-${localeCode}`,
  }));
  const normalizedValue = supportedLocales.find((localeCode) => value === localeCode || value.startsWith(`${localeCode}-`))
    ?? defaultLocale;

  return (
    <div
      className="localeSelector"
      data-testid={testId}
      style={{ '--locale-flag-background': localeFlagBackground(normalizedValue) } as CSSProperties}
    >
      <span className="localeSelector__label">{t('localeSelector.label')}</span>
      <SelectBox
        className="localeSelector__control"
        dataSource={locales}
        valueExpr="code"
        displayExpr="label"
        value={normalizedValue}
        disabled={disabled}
        width={180}
        stylingMode="outlined"
        inputAttr={{
          'aria-label': t('localeSelector.label'),
        }}
        itemRender={(item: { code: SupportedLocale; label: string; flagClass: string; testId: string }) => (
          <span className="localeSelector__item" data-testid={item.testId}>
            <span className={`localeSelector__flag ${item.flagClass}`} aria-hidden="true" />
            <span>{item.label}</span>
          </span>
        )}
        onValueChanged={(event) => {
          const nextLocale = event.value as SupportedLocale | null | undefined;

          if (nextLocale !== undefined && nextLocale !== null) {
            onChange(nextLocale);
          }
        }}
      />
    </div>
  );
}
