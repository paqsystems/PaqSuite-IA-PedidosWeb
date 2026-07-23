import { useMemo } from 'react';
import type { DataGridRowAction } from '../../../shared/ui/grids';
import type { ComprobanteConsultaRow } from '../api/consultaApi';
import { useComprobanteConsultaActions } from './useComprobanteConsultaActions';

type UseComprobanteMobileRowActionsOptions = {
  tipoOrigen?: string;
  onChanged?: () => void;
};

export function usePedidosIngresadosMobileRowActions(options: UseComprobanteMobileRowActionsOptions = {}) {
  const { openCarga, handleCopiar, handleEliminarPedido } = useComprobanteConsultaActions({
    onChanged: options.onChanged,
  });

  return useMemo<DataGridRowAction<ComprobanteConsultaRow>[]>(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: (row) => {
          openCarga(row, 'ver', options.tipoOrigen);
        },
      },
      {
        actionKey: 'editar',
        icon: 'edit',
        hintKey: 'grid.action.edit',
        visible: (row) => row.puedeEditar,
        onClick: (row) => {
          openCarga(row, 'editar', options.tipoOrigen);
        },
      },
      {
        actionKey: 'eliminar',
        icon: 'trash',
        hintKey: 'grid.action.delete',
        visible: (row) => row.puedeEliminar,
        onClick: (row) => {
          void handleEliminarPedido(row);
        },
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: (row) => row.puedeCopiar,
        onClick: (row) => {
          handleCopiar(row, options.tipoOrigen);
        },
      },
    ],
    [handleCopiar, handleEliminarPedido, openCarga, options.tipoOrigen],
  );
}

export function usePedidosPendientesMobileRowActions(options: UseComprobanteMobileRowActionsOptions = {}) {
  const { openCarga, handleCopiar } = useComprobanteConsultaActions({
    onChanged: options.onChanged,
  });

  return useMemo<DataGridRowAction<ComprobanteConsultaRow>[]>(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: (row) => {
          openCarga(row, 'ver', options.tipoOrigen);
        },
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: (row) => row.puedeCopiar,
        onClick: (row) => {
          handleCopiar(row, options.tipoOrigen);
        },
      },
    ],
    [handleCopiar, openCarga, options.tipoOrigen],
  );
}

export function usePresupuestosActivosMobileRowActions(options: UseComprobanteMobileRowActionsOptions = {}) {
  const { openCarga, handleCopiar } = useComprobanteConsultaActions({
    onChanged: options.onChanged,
  });
  const tipoOrigen = options.tipoOrigen ?? 'presupuesto';

  return useMemo<DataGridRowAction<ComprobanteConsultaRow>[]>(
    () => [
      {
        actionKey: 'ver',
        icon: 'find',
        hintKey: 'grid.action.view',
        onClick: (row) => {
          openCarga(row, 'ver', tipoOrigen);
        },
      },
      {
        actionKey: 'editar',
        icon: 'edit',
        hintKey: 'grid.action.edit',
        visible: (row) => row.puedeEditar,
        onClick: (row) => {
          openCarga(row, 'editar', tipoOrigen);
        },
      },
      {
        actionKey: 'convertir',
        icon: 'redo',
        hintKey: 'grid.action.convert',
        visible: (row) => row.puedeConvertir,
        onClick: (row) => {
          openCarga(row, 'convertir', tipoOrigen);
        },
      },
      {
        actionKey: 'copiar',
        icon: 'copy',
        hintKey: 'grid.action.copy',
        visible: (row) => row.puedeCopiar,
        onClick: (row) => {
          handleCopiar(row, tipoOrigen);
        },
      },
    ],
    [handleCopiar, openCarga, tipoOrigen],
  );
}
