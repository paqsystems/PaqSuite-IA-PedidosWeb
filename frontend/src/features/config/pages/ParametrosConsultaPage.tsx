import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../../consultas/components/ConsultaGridPage';
import { fetchParametrosConsulta, type ParametroConsultaRow } from '../api/parametrosConsultaApi';
import { mapParametroConsultaRow } from '../utils/resolveParametroConsultaTexts';

const proceso = 'pw_consultaparametros';
const gridId = 'pw_consultaparametros';

export function ParametrosConsultaPage() {
  const { t, i18n } = useTranslation();
  const loadData = useCallback(async () => {
    const result = await fetchParametrosConsulta();

    return {
      items: result.items.map((row) => mapParametroConsultaRow(t, row)),
      meta: null,
    };
  }, [t, i18n.language]);

  return (
    <ConsultaGridPage<ParametroConsultaRow>
      pageTestId="page-parametros-consulta"
      pageTitleKey="pages.consultaParametros"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={[]}
      columns={
        <>
          <Column dataField="caption" caption={t('parametros.column.caption')} />
          <Column
            dataField="valorMostrado"
            caption={t('parametros.column.valor')}
            alignment="center"
            cssClass="parametrosConsultaPage__valorColumn"
          />
          <Column dataField="tooltip" caption={t('parametros.column.tooltip')} />
          <Column dataField="tipoValor" caption={t('parametros.column.tipoValor')} visible={false} />
        </>
      }
    />
  );
}
