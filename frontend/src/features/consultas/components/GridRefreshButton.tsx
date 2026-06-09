import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';

type GridRefreshButtonProps = {
  onRefresh: () => void;
};

export function GridRefreshButton({ onRefresh }: GridRefreshButtonProps) {
  const { t } = useTranslation();

  return (
    <Button
      icon="refresh"
      stylingMode="outlined"
      hint={t('grid.refresh')}
      onClick={onRefresh}
      elementAttr={{ 'data-testid': 'gridRefresh' }}
    />
  );
}
