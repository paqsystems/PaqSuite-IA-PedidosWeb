import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import DataGrid, { Button, Column } from 'devextreme-react/data-grid';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import {
  calcularImporteNetoRenglon,
  calcularPrecioNetoUnitario,
  renglonesValidosParaGrabar,
} from '../utils/renglonesCarga';
import { PedidosCargaRenglonEditDialog } from './PedidosCargaRenglonEditDialog';

type PedidosCargaRenglonesGridProps = {
  renglones: ComprobanteRenglon[];
  readOnly: boolean;
  isLoading: boolean;
  modificaPrecio: boolean;
  modificaBonArt: boolean;
  bonificacionNetaCabecera: number;
  monedaSimbolo?: string;
  autoOpenRenglonId?: number | null;
  onAutoOpenConsumed?: () => void;
  onRenglonesChange: (renglones: ComprobanteRenglon[]) => void;
};

const gridId = 'grid-renglones-carga';

function isRenglonConArticulo(renglon: ComprobanteRenglon): boolean {
  return renglon.codArticulo.trim() !== '';
}

export function PedidosCargaRenglonesGrid({
  renglones,
  readOnly,
  isLoading,
  modificaPrecio,
  modificaBonArt,
  bonificacionNetaCabecera,
  monedaSimbolo = '$',
  autoOpenRenglonId = null,
  onAutoOpenConsumed,
  onRenglonesChange,
}: PedidosCargaRenglonesGridProps) {
  const { t } = useTranslation();
  const [renglonEnEdicion, setRenglonEnEdicion] = useState<ComprobanteRenglon | null>(null);
  const [editDialogVisible, setEditDialogVisible] = useState(false);

  const renglonesVisibles = useMemo(
    () => renglonesValidosParaGrabar(renglones),
    [renglones],
  );

  const handleEliminar = useCallback(
    (renglonId: number) => {
      onRenglonesChange(renglones.filter((renglon) => renglon.renglon !== renglonId));
    },
    [onRenglonesChange, renglones],
  );

  const handleGuardarEdicion = useCallback(
    (renglonActualizado: ComprobanteRenglon) => {
      onRenglonesChange(
        renglones.map((renglon) =>
          renglon.renglon === renglonActualizado.renglon ? { ...renglonActualizado } : renglon,
        ),
      );
    },
    [onRenglonesChange, renglones],
  );

  const abrirEdicion = useCallback((renglon: ComprobanteRenglon) => {
    setRenglonEnEdicion({ ...renglon });
    setEditDialogVisible(true);
  }, []);

  const cerrarEdicion = useCallback(() => {
    setEditDialogVisible(false);
    setRenglonEnEdicion(null);
  }, []);

  useEffect(() => {
    if (autoOpenRenglonId === null || autoOpenRenglonId === undefined) {
      return;
    }

    const renglon = renglonesVisibles.find((item) => item.renglon === autoOpenRenglonId);
    if (renglon) {
      abrirEdicion(renglon);
    }

    onAutoOpenConsumed?.();
  }, [abrirEdicion, autoOpenRenglonId, onAutoOpenConsumed, renglonesVisibles]);

  return (
    <div data-testid={gridId} className="pedidosCargaRenglonesGrid">
      <DataGrid
        dataSource={renglonesVisibles}
        keyExpr="renglon"
        showBorders={true}
        disabled={isLoading}
        columnAutoWidth={true}
        hoverStateEnabled={true}
      >
        {!readOnly ? (
          <Column
            type="buttons"
            width={88}
            caption={t('grid.column.actions')}
            allowHiding={false}
          >
            <Button
              name="renglonEditar"
              icon="edit"
              hint={t('grid.action.edit')}
              onClick={(cell) => {
                const row = cell.row?.data as ComprobanteRenglon | undefined;
                if (row && isRenglonConArticulo(row)) {
                  abrirEdicion(row);
                }
              }}
            />
            <Button
              name="renglonEliminar"
              icon="trash"
              hint={t('grid.action.delete')}
              onClick={(cell) => {
                const row = cell.row?.data as ComprobanteRenglon | undefined;
                if (row?.renglon !== undefined) {
                  handleEliminar(row.renglon);
                }
              }}
            />
          </Column>
        ) : null}
        <Column dataField="codArticulo" caption={t('pedidos.carga.grid.articulo')} allowEditing={false} />
        <Column
          dataField="descripcionArticulo"
          caption={t('pedidos.carga.grid.descripcion')}
          allowEditing={false}
        />
        <Column
          dataField="cantidad"
          caption={t('pedidos.carga.grid.cantidad')}
          dataType="number"
          allowEditing={false}
        />
        <Column
          dataField="precio"
          caption={t('pedidos.carga.grid.precio')}
          dataType="number"
          format={`${monedaSimbolo} #,##0.00`}
          allowEditing={false}
          cssClass="renglon-precio"
        />
        <Column
          dataField="porcBonif"
          caption={t('pedidos.carga.grid.bonificacion')}
          dataType="number"
          format="#0.##'%'"
          allowEditing={false}
          cssClass="renglon-bonificacion"
        />
        <Column
          caption={t('pedidos.carga.grid.precioNetoUnitario')}
          allowEditing={false}
          calculateCellValue={(row: ComprobanteRenglon) =>
            calcularPrecioNetoUnitario(row.precio, row.porcBonif, bonificacionNetaCabecera)
          }
          format={`${monedaSimbolo} #,##0.00`}
          cssClass="renglon-precio-neto-unitario"
        />
        <Column
          caption={t('pedidos.carga.grid.importeNeto')}
          allowEditing={false}
          calculateCellValue={(row: ComprobanteRenglon) =>
            calcularImporteNetoRenglon(row, bonificacionNetaCabecera)
          }
          format={`${monedaSimbolo} #,##0.00`}
        />
      </DataGrid>

      <PedidosCargaRenglonEditDialog
        visible={editDialogVisible}
        renglon={renglonEnEdicion}
        readOnly={readOnly}
        modificaPrecio={modificaPrecio}
        modificaBonArt={modificaBonArt}
        bonificacionNetaCabecera={bonificacionNetaCabecera}
        monedaSimbolo={monedaSimbolo}
        onClose={cerrarEdicion}
        onSave={handleGuardarEdicion}
      />
    </div>
  );
}
