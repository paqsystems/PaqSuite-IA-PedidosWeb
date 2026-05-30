import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import DataGrid, { Column, FilterRow } from 'devextreme-react/data-grid';

type DemoRow = {
  id: number;
  name: string;
};

export function LocaleDemoGrid() {
  const { t } = useTranslation();

  const rows = useMemo<DemoRow[]>(
    () => [
      { id: 1, name: 'Alpha' },
      { id: 2, name: 'Beta' },
    ],
    [],
  );

  return (
    <div data-testid="localeDemoGrid">
      <DataGrid dataSource={rows} keyExpr="id" showBorders={true}>
        <FilterRow visible={true} />
        <Column dataField="name" caption={t('demoGrid.columnName')} />
      </DataGrid>
    </div>
  );
}
