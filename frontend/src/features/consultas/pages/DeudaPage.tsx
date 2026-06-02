import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../components/ConsultaGridPage';
import { fetchDeuda, type DeudaConsultaRow } from '../api/consultaApi';

const proceso = 'pw_deuda';
const gridId = 'pw_deuda';

export function DeudaPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchDeuda(), []);

  return (
    <ConsultaGridPage<DeudaConsultaRow>
      pageTestId="page-consulta-deuda"
      pageTitleKey="pages.consultaDeuda"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={[]}
      columns={
        <>
          <Column dataField="cliente" caption={t('consultas.column.cliente')} />
          <Column dataField="vencimiento" caption={t('consultas.column.vencimiento')} />
          <Column dataField="importe" caption={t('consultas.column.importe')} dataType="number" format="currency" />
        </>
      }
    />
  );
}
