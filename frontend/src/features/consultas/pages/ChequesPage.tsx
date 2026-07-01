import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
import { ConsultaInformePivotPage } from '../components/ConsultaInformePivotPage';
import { fetchCheques, type ChequeConsultaRow } from '../api/consultaApi';
import { getChequeDetailFields, renderChequeCard } from '../components/consultaMobileRenderers';

const proceso = 'pw_cheques';
const gridId = 'pw_cheques';
const pivotConsultaId = 'CONSULTA_CHEQUES';

export function ChequesPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchCheques(), []);

  if (isNativeApp()) {
    return (
      <ConsultaKardexMobileView
        mode="client"
        pageTestId="page-consulta-cheques-mobile"
        pageTitleKey="pages.consultaCheques"
        listTestId="chequesKardexList"
        keyExpr="id"
        loadData={loadData}
        detailTitle={(item) => item.numero}
        detailFields={getChequeDetailFields()}
        renderCard={(item) => renderChequeCard(item, t)}
      />
    );
  }

  return (
    <ConsultaInformePivotPage<ChequeConsultaRow>
      pageTestId="page-consulta-cheques"
      pageTitleKey="pages.consultaCheques"
      proceso={proceso}
      gridId={gridId}
      pivotConsultaId={pivotConsultaId}
      testIdPrefix="consultaCheques"
      loadData={loadData}
      columns={
        <>
          <Column dataField="interno" caption={t('consultas.column.interno')} />
          <Column dataField="numero" caption={t('consultas.column.numero')} />
          <Column dataField="codCliente" caption={t('consultas.column.cliente')} />
          <Column dataField="nombreCliente" caption={t('consultas.column.nombre')} />
          <Column dataField="banco" caption={t('consultas.column.banco')} />
          <Column
            dataField="fecha"
            caption={t('consultas.column.fecha')}
            dataType="date"
            format="dd/MM/yyyy"
          />
          <Column
            dataField="importe"
            caption={t('consultas.column.importe')}
            dataType="number"
            format="#,##0.00"
          />
          <Column dataField="origen" caption={t('consultas.column.origen')} />
          <Column dataField="estado" caption={t('consultas.column.estado')} />
        </>
      }
    />
  );
}
