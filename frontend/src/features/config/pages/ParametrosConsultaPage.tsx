import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ConsultaKardexMobileView } from '../../../shared/consultas/ConsultaKardexMobileView';
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

  if (isNativeApp()) {
    return (
      <ConsultaKardexMobileView
        mode="client"
        pageTestId="page-parametros-consulta-mobile"
        pageTitleKey="pages.consultaParametros"
        listTestId="parametrosKardexList"
        keyExpr="id"
        loadData={loadData}
        detailTitle={(item) => item.caption}
        detailFields={[
          { labelKey: 'parametros.column.caption', getValue: (item) => item.caption },
          { labelKey: 'parametros.column.valor', getValue: (item) => item.valorMostrado },
          {
            labelKey: 'parametros.column.tooltip',
            getValue: (item) => item.tooltip || '—',
            visible: (item) => item.tooltip.trim().length > 0,
          },
        ]}
        renderCard={(item) => (
          <article className="consultaKardexCard">
            <div className="consultaKardexCard__title">{item.caption}</div>
            <div className="consultaKardexCard__subtitle">{item.valorMostrado}</div>
          </article>
        )}
      />
    );
  }

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
