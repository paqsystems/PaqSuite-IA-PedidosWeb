import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { Column } from 'devextreme-react/data-grid';
import { ConsultaGridPage } from '../../consultas/components/ConsultaGridPage';
import { fetchParametrosConsulta, type ParametroConsultaRow } from '../api/parametrosConsultaApi';

const proceso = 'pw_consultaparametros';
const gridId = 'pw_consultaparametros';

export function ParametrosConsultaPage() {
  const { t } = useTranslation();
  const loadData = useCallback(async () => {
    const result = await fetchParametrosConsulta();

    return {
      items: result.items,
      meta: null,
    };
  }, []);

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
            cellRender={(cell) => {
              const row = cell.data as ParametroConsultaRow | undefined;

              if (!row) {
                return cell.value ?? '';
              }

              if (row.tipoValor === 'B') {
                const truthy = row.valorMostrado === 'true' || row.valorMostrado === '1';

                return truthy ? t('pedidos.carga.cabecera.si') : t('pedidos.carga.cabecera.no');
              }

              if (row.tipoValor === 'D' && row.valorMostrado) {
                const date = new Date(row.valorMostrado);

                if (!Number.isNaN(date.getTime())) {
                  return date.toLocaleDateString();
                }
              }

              return row.valorMostrado;
            }}
          />
          <Column dataField="tooltip" caption={t('parametros.column.tooltip')} />
          <Column dataField="tipoValor" caption={t('parametros.column.tipoValor')} visible={false} />
        </>
      }
    />
  );
}
