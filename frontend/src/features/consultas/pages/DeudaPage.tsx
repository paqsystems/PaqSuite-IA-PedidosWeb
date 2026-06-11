import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaInformePivotPage } from '../components/ConsultaInformePivotPage';
import { fetchDeuda, type DeudaConsultaRow } from '../api/consultaApi';

const proceso = 'pw_deuda';
const gridId = 'pw_deuda';
const pivotConsultaId = 'CONSULTA_DEUDA';

export function DeudaPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchDeuda(), []);

  return (
    <ConsultaInformePivotPage<DeudaConsultaRow>
      pageTestId="page-consulta-deuda"
      pageTitleKey="pages.consultaDeuda"
      proceso={proceso}
      gridId={gridId}
      pivotConsultaId={pivotConsultaId}
      testIdPrefix="consultaDeuda"
      loadData={loadData}
      columns={
        <>
          <Column dataField="codCliente" caption={t('consultas.column.cliente')} />
          <Column dataField="razonSocial" caption={t('consultas.column.razonSocial')} />
          <Column dataField="tipo" caption={t('consultas.column.tipo')} />
          <Column dataField="numero" caption={t('consultas.column.numero')} />
          <Column
            dataField="fecha"
            caption={t('consultas.column.fecha')}
            dataType="date"
            format="dd/MM/yyyy"
          />
          <Column
            dataField="vencimiento"
            caption={t('consultas.column.vencimiento')}
            dataType="date"
            format="dd/MM/yyyy"
          />
          <Column
            dataField="saldo"
            caption={t('consultas.column.saldo')}
            dataType="number"
            format="#,##0.00"
          />
        </>
      }
    />
  );
}
