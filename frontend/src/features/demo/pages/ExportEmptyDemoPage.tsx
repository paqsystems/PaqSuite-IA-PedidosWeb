import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { DataGridDx } from '../../../shared/ui/grids';

const exportEmptyProceso = 'pw_export_empty';
const exportEmptyGridId = 'main';

export function ExportEmptyDemoPage() {
  const { t } = useTranslation();

  return (
    <section data-testid="process-export-empty">
      <h2>{t('gridExport.emptyDemo.title')}</h2>
      <p>{t('gridExport.emptyDemo.description')}</p>
      <DataGridDx
        proceso={exportEmptyProceso}
        gridId={exportEmptyGridId}
        dataSource={[]}
        keyExpr="id"
      >
        <Column dataField="id" caption={t('grid.column.id')} width={80} />
        <Column dataField="name" caption={t('grid.column.name')} />
      </DataGridDx>
    </section>
  );
}
