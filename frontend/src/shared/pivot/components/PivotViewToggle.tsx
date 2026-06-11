import { useTranslation } from 'react-i18next';
import ButtonGroup from 'devextreme-react/button-group';
import type { PivotViewMode } from '../types/pivotViewMode';

type PivotViewToggleProps = {
  value: PivotViewMode;
  onChange: (mode: PivotViewMode) => void;
  testIdPrefix: string;
};

export function PivotViewToggle({ value, onChange, testIdPrefix }: PivotViewToggleProps) {
  const { t } = useTranslation();
  const gridLabel = t('pivot.view.grid');
  const pivotLabel = t('pivot.view.pivot');

  return (
    <div data-testid="pivotViewToggle">
      <ButtonGroup
        items={[{ text: gridLabel }, { text: pivotLabel }]}
        keyExpr="text"
        selectedItemKeys={[value === 'grid' ? gridLabel : pivotLabel]}
        selectionMode="single"
        onItemClick={(event) => {
          const label = event.itemData?.text;

          if (label === gridLabel) {
            onChange('grid');
          }

          if (label === pivotLabel) {
            onChange('pivot');
          }
        }}
        elementAttr={{
          'data-testid': `${testIdPrefix}.viewToggle`,
        }}
      />
    </div>
  );
}
