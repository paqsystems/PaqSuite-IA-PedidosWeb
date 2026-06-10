import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ConsultaGridPage } from '../../consultas/components/ConsultaGridPage';
import { ComprobanteConsultaColumns } from '../../consultas/components/ComprobanteConsultaColumns';
import { useComprobanteConsultaActions } from '../../consultas/hooks/useComprobanteConsultaActions';
import { fetchPedidosPendientes, type PedidoConsultaRow } from '../../consultas/api/consultaApi';
import type { DataGridRowAction } from '../../../shared/ui/grids';

const proceso = 'pw_pedidospendientes';
const gridId = 'pw_pedidospendientes';

export function PedidosPendientesPage() {
  const { t } = useTranslation();
  const [refreshToken, setRefreshToken] = useState(0);
  const reloadGrid = useCallback(() => {
    setRefreshToken((value) => value + 1);
  }, []);
  const { openCarga, handleCopiar } = useComprobanteConsultaActions({ onChanged: reloadGrid });
  const loadData = useCallback(() => fetchPedidosPendientes(), []);

  const rowActions: DataGridRowAction<PedidoConsultaRow>[] = useMemo(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: (row) => {
          openCarga(row, 'ver');
        },
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: (row) => row.puedeCopiar,
        onClick: (row) => {
          handleCopiar(row);
        },
      },
    ],
    [handleCopiar, openCarga],
  );

  return (
    <ConsultaGridPage<PedidoConsultaRow>
      pageTestId="page-pedidos-pendientes"
      pageTitleKey="pages.pedidosPendientes"
      proceso={proceso}
      gridId={gridId}
      loadData={loadData}
      rowActions={rowActions}
      refreshToken={refreshToken}
      columns={<ComprobanteConsultaColumns t={t} />}
    />
  );
}
