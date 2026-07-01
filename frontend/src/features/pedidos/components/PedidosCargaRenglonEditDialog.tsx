import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import NumberBox from 'devextreme-react/number-box';
import Popup from 'devextreme-react/popup';
import TextBox from 'devextreme-react/text-box';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import { bonificacionCabeceraFormat } from '../constants/cabeceraCatalogos';
import {
  calcularImporteBrutoRenglon,
  calcularImporteIvaRenglon,
  calcularImporteNetoConIvaRenglon,
  calcularImporteNetoRenglon,
  formatImporteMoneda,
} from '../utils/renglonesCarga';
import '../pages/PedidosCargaPage.css';

type PedidosCargaRenglonEditDialogProps = {
  visible: boolean;
  renglon: ComprobanteRenglon | null;
  readOnly: boolean;
  modificaPrecio: boolean;
  modificaBonArt: boolean;
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
      wrapperAttr={{ class: 'pedidosCargaRenglonEditPopup' }}
      elementAttr={{ 'data-testid': 'dialog-editar-renglon' }}
    >
      <div className="pedidosCargaRenglonEditDialog">
        <TextBox
          label={t('pedidos.carga.grid.articulo')}
          value={draft.codArticulo}
          readOnly={true}
          inputAttr={{ 'data-testid': 'renglon-edit-articulo' }}
        />
        <TextBox
          label={t('pedidos.carga.grid.descripcion')}
          value={draft.descripcionArticulo ?? ''}
          readOnly={true}
        />
        <NumberBox
          label={t('pedidos.carga.grid.cantidad')}
          value={draft.cantidad}
          min={0.0001}
          readOnly={!canEdit}
          onValueChanged={(event) => {
            setDraft((previous) =>
              previous ? { ...previous, cantidad: Number(event.value ?? 0) } : previous,
            );
          }}
          inputAttr={{ 'data-testid': 'renglon-edit-cantidad' }}
        />
        <NumberBox
          label={t('pedidos.carga.grid.precio')}
          value={draft.precio}
          min={0}
          format={`${monedaSimbolo} #,##0.00`}
          disabled={!canEdit || !modificaPrecio}
          onValueChanged={(event) => {
            setDraft((previous) =>
              previous ? { ...previous, precio: Number(event.value ?? 0) } : previous,
            );
          }}
          inputAttr={{ 'data-testid': 'renglon-precio' }}
        />
        <NumberBox
          label={t('pedidos.carga.grid.bonificacion')}
          value={draft.porcBonif}
          min={0}
          max={100}
          format={bonificacionCabeceraFormat}
          step={0.01}
          showSpinButtons={true}
          disabled={!canEdit || !modificaBonArt}
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
            value={importes.bruto}
            readOnly={true}
            inputAttr={{ 'data-testid': 'renglon-importe-bruto' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.importeNeto')}
            value={importes.neto}
            readOnly={true}
            inputAttr={{ 'data-testid': 'renglon-importe-neto' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.importeIva')}
            value={importes.iva}
            readOnly={true}
            inputAttr={{ 'data-testid': 'renglon-importe-iva' }}
          />
          <TextBox
            label={t('pedidos.carga.renglon.importeNetoConIva')}
            value={importes.netoConIva}
            readOnly={true}
            elementAttr={{ class: 'pedidosCargaRenglonEditDialog__importeDestacado' }}
            inputAttr={{ 'data-testid': 'renglon-importe-neto-con-iva' }}
          />
        </div>
        {canEdit ? (
          <div className="pedidosCargaRenglonEditDialog__actions">
            <Button
              text={t('pedidos.carga.renglon.guardar')}
              type="default"
              disabled={draft.cantidad <= 0}
              onClick={handleConfirm}
              elementAttr={{ 'data-testid': 'renglon-edit-guardar' }}
            />
            <Button
              text={t('pedidos.carga.cancelar')}
              stylingMode="text"
              onClick={onClose}
              elementAttr={{ 'data-testid': 'renglon-edit-cancelar' }}
            />
          </div>
        ) : null}
      </div>
    </Popup>
  );
}
