import { useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { confirmDelete } from '../../../shared/ui/abm';
import { eliminarPedido } from '../../pedidos/api/comprobanteApi';
import type { ComprobanteConsultaRow } from '../../consultas/api/consultaApi';

type UseComprobanteConsultaActionsOptions = {
  onChanged?: () => void;
};

export function buildCargaUrl(codPedido: string, modo: string, tipoOrigen?: string): string {
  const params = new URLSearchParams({
    codComprobante: codPedido,
    modo,
  });

  if (tipoOrigen) {
    params.set('tipoOrigen', tipoOrigen);
  }

  return `/pedidos/carga?${params.toString()}`;
}

export function useComprobanteConsultaActions(options: UseComprobanteConsultaActionsOptions = {}) {
  const navigate = useNavigate();
  const { t } = useTranslation();

  const openCarga = useCallback(
    (row: ComprobanteConsultaRow, modo: 'ver' | 'editar' | 'copia' | 'convertir', tipoOrigen?: string) => {
      navigate(buildCargaUrl(row.codPedido, modo, tipoOrigen));
    },
    [navigate],
  );

  const handleCopiar = useCallback(
    (row: ComprobanteConsultaRow, tipoOrigen?: string) => {
      openCarga(row, 'copia', tipoOrigen);
    },
    [openCarga],
  );

  const handleEliminarPedido = useCallback(
    async (row: ComprobanteConsultaRow) => {
      const confirmed = await confirmDelete({
        recordLabel: row.numero || row.codPedido,
        t,
      });

      if (!confirmed) {
        return;
      }

      await eliminarPedido(row.codPedido);
      options.onChanged?.();
    },
    [options, t],
  );

  return {
    openCarga,
    handleCopiar,
    handleEliminarPedido,
  };
}
