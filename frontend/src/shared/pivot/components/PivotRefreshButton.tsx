import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';

type PivotRefreshButtonProps = {
  onRefresh: () => void;
};

export function PivotRefreshButton({ onRefresh }: PivotRefreshButtonProps) {
  const { t } = useTranslation();

  return (
    <Button
      icon="refresh"
      stylingMode="outlined"
      hint={t('grid.refresh')}
      onClick={onRefresh}
      elementAttr={{ 'data-testid': 'pivotRefresh' }}
    />
  );
}
