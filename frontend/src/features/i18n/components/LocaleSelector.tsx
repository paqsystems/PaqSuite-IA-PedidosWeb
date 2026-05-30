import { useTranslation } from 'react-i18next';
import { supportedLocales } from '../model/supportedLocales';

type LocaleSelectorProps = {
  value: string;
  onChange: (locale: string) => void;
  testId: string;
  disabled?: boolean;
};

export function LocaleSelector({ value, onChange, testId, disabled = false }: LocaleSelectorProps) {
  const { t } = useTranslation();

  return (
    <label className="localeSelector" data-testid={testId}>
      {t('localeSelector.label')}
      <select
        value={value}
        disabled={disabled}
        aria-label={t('localeSelector.label')}
        onChange={(event) => onChange(event.target.value)}
      >
        {supportedLocales.map((localeCode) => (
          <option
            key={localeCode}
            value={localeCode}
            data-testid={`localeOption-${localeCode}`}
          >
            {t(`locale.name.${localeCode}`)}
          </option>
        ))}
      </select>
    </label>
  );
}
