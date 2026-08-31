import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import NumberBox from 'devextreme-react/number-box';
import Popup from 'devextreme-react/popup';
import TextBox from 'devextreme-react/text-box';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import { bonificacionCabeceraFormat } from '../constants/cabeceraCatalogos';
import {
  applyCantidadUsuarioToRenglon,
  cantidadVisibleParaUsuario,
  resolveEquivalenciaVentas,
} from '../utils/cargaUnidadesVenta';
import {
  calcularImporteBrutoRenglon,
  calcularImporteIvaRenglon,
  calcularImporteNetoConIvaRenglon,
  calcularImporteNetoRenglon,
  calcularPrecioNetoUnitario,
  formatImporteMoneda,
} from '../utils/renglonesCarga';
import '../pages/PedidosCargaPage.css';

type PedidosCargaRenglonEditDialogProps = {
  visible: boolean;
  renglon: ComprobanteRenglon | null;
  readOnly: boolean;
  modificaPrecio: boolean;
  modificaBonArt: boolean;
  cargaUnidadesVenta?: boolean;
  bonificacionNetaCabecera: number;
  monedaSimbolo?: string;
  onClose: () => void;
  onSave: (renglon: ComprobanteRenglon) => void;
};

export function PedidosCargaRenglonEditDialog({
  visible,
  renglon,
  readOnly,
  modificaPrecio,
  modificaBonArt,
  cargaUnidadesVenta = false,
  bonificacionNetaCabecera,
  monedaSimbolo = '$',
  onClose,
  onSave,
}: PedidosCargaRenglonEditDialogProps) {
  const { t } = useTranslation();
  const [draft, setDraft] = useState<ComprobanteRenglon | null>(null);

  useEffect(() => {
    if (visible && renglon) {
      setDraft({ ...renglon });
    }
  }, [renglon, visible]);

  const importes = useMemo(() => {
    if (!draft) {
      return null;
    }

    const bruto = calcularImporteBrutoRenglon(draft);
    const neto = calcularImporteNetoRenglon(draft, bonificacionNetaCabecera);
    const iva = calcularImporteIvaRenglon(draft, bonificacionNetaCabecera);
    const netoConIva = calcularImporteNetoConIvaRenglon(draft, bonificacionNetaCabecera);

    return {
      bruto: formatImporteMoneda(monedaSimbolo, bruto),
      neto: formatImporteMoneda(monedaSimbolo, neto),
      iva: formatImporteMoneda(monedaSimbolo, iva),
      netoConIva: formatImporteMoneda(monedaSimbolo, netoConIva),
    };
  }, [bonificacionNetaCabecera, draft, monedaSimbolo]);

  if (!draft || !importes) {
    return null;
  }

  const canEdit = !readOnly;
  const cantidadVisible = cantidadVisibleParaUsuario(
    draft.cantidad,
    draft.cantidadVenta,
    cargaUnidadesVenta,
  );
  const equivalenciaVentas = resolveEquivalenciaVentas(draft.equivalenciaVentas);
  const unidadesStockEquivalentes = cargaUnidadesVenta
    ? Number((cantidadVisible * equivalenciaVentas).toFixed(4))
    : null;
  const precioNetoUnitario = calcularPrecioNetoUnitario(
    draft.precio,
    draft.porcBonif,
    bonificacionNetaCabecera,
  );

  const handleConfirm = () => {
    if (!canEdit || draft.cantidad <= 0) {
      return;
    }

    onSave(draft);
    onClose();
  };

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      dragEnabled={false}
      showCloseButton={true}
      height="auto"
      title={t('pedidos.carga.renglon.editarTitulo')}
      wrapperAttr={{ class: 'pedidosCargaRenglonEditPopup pedidosCargaDialogPopup' }}
      elementAttr={{ 'data-testid': 'dialog-editar-renglon' }}
    >
      <div className="pedidosCargaRenglonEditDialog">
        <TextBox
          label={t('pedidos.carga.grid.articulo')}
          labelMode="outside"
          value={draft.codArticulo}
          readOnly={true}
          stylingMode="outlined"
          width="100%"
          inputAttr={{ 'data-testid': 'renglon-edit-articulo' }}
        />
        <TextBox
          label={t('pedidos.carga.grid.descripcion')}
          labelMode="outside"
          value={draft.descripcionArticulo ?? ''}
          readOnly={true}
          stylingMode="outlined"
          width="100%"
        />
        <div
          className={
            cargaUnidadesVenta
              ? 'pedidosCargaRenglonEditDialog__row pedidosCargaRenglonEditDialog__row--compact'
              : undefined
          }
        >
          <NumberBox
            label={t('pedidos.carga.grid.cantidad')}
            labelMode="outside"
            value={cantidadVisible}
            min={0.0001}
            readOnly={!canEdit}
            stylingMode="outlined"
            width="100%"
            onValueChanged={(event) => {
              const cantidadUsuario = Number(event.value ?? 0);
              setDraft((previous) =>
                previous
                  ? applyCantidadUsuarioToRenglon(previous, cantidadUsuario, cargaUnidadesVenta)
                  : previous,
              );
            }}
            inputAttr={{ 'data-testid': 'renglon-edit-cantidad' }}
          />
          {cargaUnidadesVenta && unidadesStockEquivalentes !== null ? (
            <TextBox
              label={t('pedidos.carga.renglon.unidadesStockEquivalentes')}
              labelMode="outside"
              value={String(unidadesStockEquivalentes)}
              readOnly={true}
              stylingMode="outlined"
              width="100%"
              inputAttr={{ 'data-testid': 'renglon-unidades-stock-equiv' }}
            />
          ) : null}
        </div>
        <div className="pedidosCargaRenglonEditDialog__row pedidosCargaRenglonEditDialog__row--compact">
          <NumberBox
            label={t('pedidos.carga.grid.precio')}
            labelMode="outside"
            value={draft.precio}
            min={0}
            format={`${monedaSimbolo} #,##0.00`}
            disabled={!canEdit || !modificaPrecio}
            stylingMode="outlined"
            width="100%"
            onValueChanged={(event) => {
              setDraft((previous) =>
                previous ? { ...previous, precio: Number(event.value ?? 0) } : previous,
              );
            }}
            inputAttr={{ 'data-testid': 'renglon-precio' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.precioNetoUnitario')}
            labelMode="outside"
            value={formatImporteMoneda(monedaSimbolo, precioNetoUnitario)}
            readOnly={true}
            stylingMode="outlined"
            width="100%"
            inputAttr={{ 'data-testid': 'renglon-precio-neto-unitario' }}
          />
        </div>
        <NumberBox
          label={t('pedidos.carga.grid.bonificacion')}
          labelMode="outside"
          value={draft.porcBonif}
          min={0}
          max={100}
          format={bonificacionCabeceraFormat}
          step={0.01}
          showSpinButtons={true}
          disabled={!canEdit || !modificaBonArt}
          stylingMode="outlined"
          width="100%"
          onValueChanged={(event) => {
            setDraft((previous) =>
              previous ? { ...previous, porcBonif: Number(event.value ?? 0) } : previous,
            );
          }}
          inputAttr={{ 'data-testid': 'renglon-bonificacion' }}
        />
        <div className="pedidosCargaRenglonEditDialog__importes">
          <TextBox
            label={t('pedidos.carga.renglon.importeBruto')}
            labelMode="outside"
            value={importes.bruto}
            readOnly={true}
            stylingMode="outlined"
            width="100%"
            inputAttr={{ 'data-testid': 'renglon-importe-bruto' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.importeNeto')}
            labelMode="outside"
            value={importes.neto}
            readOnly={true}
            stylingMode="outlined"
            width="100%"
            inputAttr={{ 'data-testid': 'renglon-importe-neto' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.importeIva')}
            labelMode="outside"
            value={importes.iva}
            readOnly={true}
            stylingMode="outlined"
            width="100%"
            inputAttr={{ 'data-testid': 'renglon-importe-iva' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.importeNetoConIva')}
            labelMode="outside"
            value={importes.netoConIva}
            readOnly={true}
            stylingMode="outlined"
            width="100%"
            elementAttr={{ class: 'pedidosCargaRenglonEditDialog__importeDestacado' }}
            inputAttr={{ 'data-testid': 'renglon-importe-neto-con-iva' }}
          />
        </div>
        {canEdit ? (
          <div className="pedidosCargaRenglonEditDialog__actions">
            <Button
              text={t('pedidos.carga.renglon.guardar')}
              type="default"
              stylingMode="contained"
              disabled={draft.cantidad <= 0}
              onClick={handleConfirm}
              elementAttr={{ 'data-testid': 'renglon-edit-guardar' }}
            />
            <Button
              text={t('pedidos.carga.cancelar')}
              stylingMode="outlined"
              onClick={onClose}
              elementAttr={{ 'data-testid': 'renglon-edit-cancelar' }}
            />
          </div>
        ) : null}
      </div>
    </Popup>
  );
}
