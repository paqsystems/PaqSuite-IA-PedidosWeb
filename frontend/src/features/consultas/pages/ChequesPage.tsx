import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../components/ConsultaGridPage';
import { fetchCheques, type ChequeConsultaRow } from '../api/consultaApi';

const proceso = 'pw_cheques';
const gridId = 'pw_cheques';

export function ChequesPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchCheques(), []);

  return (
    <ConsultaGridPage<ChequeConsultaRow>
      pageTestId="page-consulta-cheques"
      pageTitleKey="pages.consultaCheques"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={[]}
      columns={
        <>
          <Column dataField="cliente" caption={t('consultas.column.cliente')} />
          <Column dataField="banco" caption={t('consultas.column.banco')} />
          <Column dataField="vencimiento" caption={t('consultas.column.vencimiento')} />
          <Column dataField="importe" caption={t('consultas.column.importe')} dataType="number" format="currency" />
        </>
      }
    />
  );
}
