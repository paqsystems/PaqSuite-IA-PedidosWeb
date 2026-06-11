import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaInformePivotPage } from '../components/ConsultaInformePivotPage';
import { fetchStock, type StockConsultaRow } from '../api/consultaApi';

const proceso = 'pw_stock';
const gridId = 'pw_stock';
const pivotConsultaId = 'CONSULTA_STOCK';

export function StockPage() {
  const { t } = useTranslation();
  const loadData = useCallback(() => fetchStock(), []);

  return (
    <ConsultaInformePivotPage<StockConsultaRow>
      pageTestId="page-consulta-stock"
      pageTitleKey="pages.consultaStock"
      proceso={proceso}
      gridId={gridId}
      pivotConsultaId={pivotConsultaId}
      testIdPrefix="consultaStock"
      loadData={loadData}
      columns={
        <>
          <Column dataField="codArticulo" caption={t('consultas.column.codArticulo')} />
          <Column dataField="descripcion" caption={t('consultas.column.descripcion')} />
          <Column dataField="stock" caption={t('consultas.column.stock')} dataType="number" format="#,##0.00" />
          <Column
            dataField="comprometido"
            caption={t('consultas.column.comprometido')}
            dataType="number"
            format="#,##0.00"
          />
          <Column
            dataField="comprometidoWeb"
            caption={t('consultas.column.comprometidoWeb')}
            dataType="number"
            format="#,##0.00"
          />
          <Column
            dataField="disponibleNeto"
            caption={t('consultas.column.disponibleNeto')}
            dataType="number"
            format="#,##0.00"
          />
          <Column dataField="codBase" caption={t('consultas.column.codBase')} />
          <Column
            dataField="stockBase"
            caption={t('consultas.column.stockBase')}
            dataType="number"
            format="#,##0.00"
          />
          <Column
            dataField="comprometidoBase"
            caption={t('consultas.column.comprometidoBase')}
            dataType="number"
            format="#,##0.00"
          />
          <Column
            dataField="comprometidoBaseWeb"
            caption={t('consultas.column.comprometidoBaseWeb')}
            dataType="number"
            format="#,##0.00"
          />
          <Column
            dataField="disponibleNetoBase"
            caption={t('consultas.column.disponibleNetoBase')}
            dataType="number"
            format="#,##0.00"
          />
        </>
      }
    />
  );
}
