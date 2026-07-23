import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import { SelectBoxDx } from '../../../shared/ui/controls/SelectBoxDx';
import { isDevExtremeUserChange } from '../../../shared/ui/devextremeUserChange';
import { CargaAsistenteIaPanel } from '../cargaAsistenteIa/components/CargaAsistenteIaPanel';
import {
  buildCargaAsistenteDraftContext,
  mapFunctionalProfileToPerfilUsuario,
} from '../cargaAsistenteIa/utils/buildCargaAsistenteDraftContext';
import type { CargaAsistenteAddRenglonPayload } from '../cargaAsistenteIa/utils/applyCargaAsistenteActions';
import { patchAsistenteCabecera } from '../cargaAsistenteIa/utils/patchAsistenteCabecera';
import { PedidosCargaArticulosStockLoadPanel } from '../components/PedidosCargaArticulosStockLoadPanel';
import { PedidosCargaMobileCabeceraStep } from '../components/mobile/PedidosCargaMobileCabeceraStep';
import { PedidosCargaConfirmacionDialog } from '../components/PedidosCargaConfirmacionDialog';
import { PedidosCargaErroresGrabacionDialog } from '../components/PedidosCargaErroresGrabacionDialog';
import { PedidosCargaRenglonEditDialog } from '../components/PedidosCargaRenglonEditDialog';
import {
  usePedidosCargaMobile,
  type PedidosCargaMobileStep,
} from '../hooks/usePedidosCargaMobile';
import { useRequiredSessionContext } from '../../auth/AuthProvider';
import type { ComprobanteRenglon } from '../api/comprobanteApi';
import {
  calcularImporteNetoConIvaRenglon,
  createEmptyRenglon,
  formatImporteMoneda,
  nextRenglonNumber,
  renglonesValidosParaGrabar,
} from '../utils/renglonesCarga';
import './PedidosCargaMobilePage.css';

const monedaSimbolo = '$';

const stepLabelKeys: Record<PedidosCargaMobileStep, string> = {
  cliente: 'pedidos.carga.mobile.stepCliente',
  cabecera: 'pedidos.carga.mobile.stepCabecera',
  articulos: 'pedidos.carga.mobile.stepArticulos',
  confirmar: 'pedidos.carga.mobile.stepConfirmar',
};

export function PedidosCargaMobilePage() {
  const { t } = useTranslation();
  const sessionContext = useRequiredSessionContext();
  const carga = usePedidosCargaMobile();

  const stepIndex = carga.stepOrder.indexOf(carga.step);

  const buildAsistenteDraftContext = useCallback(
    () =>
      buildCargaAsistenteDraftContext({
        selectedCliente: carga.selectedCliente,
        cabecera: carga.cabecera,
        renglones: carga.renglones,
        readOnly: carga.readOnly,
        modo: carga.modo,
        perfilUsuario: mapFunctionalProfileToPerfilUsuario(sessionContext.functionalProfile),
      }),
    [
      carga.cabecera,
      carga.modo,
      carga.readOnly,
      carga.renglones,
      carga.selectedCliente,
      sessionContext.functionalProfile,
    ],
  );

  const handleAsistenteAddRenglon = useCallback(
    (payload: CargaAsistenteAddRenglonPayload) => {
      carga.setRenglones((current) => {
        const sinVacios = renglonesValidosParaGrabar(current);
        const nuevoRenglon: ComprobanteRenglon = {
          renglon: nextRenglonNumber(sinVacios),
          codArticulo: payload.codArticulo,
          descripcionArticulo: payload.descripcion ?? '',
          cantidad: payload.cantidad,
          precio: payload.precio ?? 0,
          porcBonif: payload.porcBonif ?? 0,
          porcIva: 21,
        };

        return [...sinVacios, nuevoRenglon];
      });
    },
    [carga.setRenglones],
  );

  const handleAsistenteUpdateRenglon = useCallback(
    (payload: { renglon: number; cantidad?: number; precio?: number; porcBonif?: number }) => {
      carga.setRenglones((current) =>
        current.map((row) => {
          if (row.renglon !== payload.renglon) {
            return row;
          }

          return {
            ...row,
            cantidad: payload.cantidad !== undefined ? payload.cantidad : row.cantidad,
            precio: payload.precio !== undefined ? payload.precio : row.precio,
            porcBonif: payload.porcBonif !== undefined ? payload.porcBonif : row.porcBonif,
          };
        }),
      );
    },
    [carga.setRenglones],
  );

  const handleAsistenteRemoveRenglon = useCallback(
    (renglon: number) => {
      carga.setRenglones((current) => current.filter((row) => row.renglon !== renglon));
    },
    [carga.setRenglones],
  );

  const handleAsistenteUpdateCabeceraField = useCallback(
    (field: string, value: unknown) => {
      carga.setCabecera((current) => {
        if (!current) {
          return current;
        }

        return patchAsistenteCabecera(current, { [field]: value });
      });
    },
    [carga.setCabecera],
  );

  const handleAsistentePatchCabeceraFields = useCallback(
    (fields: Record<string, unknown>) => {
      carga.setCabecera((current) => {
        if (!current) {
          return current;
        }

        return patchAsistenteCabecera(current, fields);
      });
    },
    [carga.setCabecera],
  );

  const handleAsistenteApplyImageExtract = useCallback(
    (payload: Record<string, unknown>) => {
      const renglonesValidos = Array.isArray(payload.renglonesValidos)
        ? payload.renglonesValidos
        : [];

      if (renglonesValidos.length === 0) {
        return;
      }

      carga.setRenglones((current) => {
        let next = renglonesValidosParaGrabar(current);

        for (const item of renglonesValidos) {
          if (!item || typeof item !== 'object') {
            continue;
          }

          const row = item as Record<string, unknown>;
          const codArticulo = String(row.codArticulo ?? '').trim();
          if (codArticulo === '') {
            continue;
          }

          next = [
            ...next,
            {
              renglon: nextRenglonNumber(next),
              codArticulo,
              descripcionArticulo: String(row.descripcion ?? ''),
              cantidad: Number(row.cantidad) > 0 ? Number(row.cantidad) : 1,
              precio: row.precio !== undefined ? Number(row.precio) : 0,
              porcBonif: row.porcBonif !== undefined ? Number(row.porcBonif) : 0,
              porcIva: 21,
            },
          ];
        }

        return next.length > 0 ? next : [createEmptyRenglon(1)];
      });
    },
    [carga.setRenglones],
  );

  return (
    <section className="pedidosCargaMobilePage" data-testid="page-pedidos-carga-mobile">
      <header className="pedidosCargaMobilePage__header">
        <h1>{t('pages.pedidosCarga')}</h1>
        <p className="pedidosCargaMobilePage__tipo" data-testid="label-tipo-comprobante">
          {carga.tipoComprobanteLabel}
        </p>
      </header>

      {carga.readOnly ? (
        <p data-testid="label-modo-solo-lectura">{t('pedidos.carga.modoSoloLectura')}</p>
      ) : null}

      <div className="pedidosCargaMobilePage__steps" aria-label={t('pedidos.carga.mobile.stepsAria')}>
        {carga.stepOrder.map((stepId, index) => {
          const isActive = stepId === carga.step;
          const isDone = index < stepIndex;

          return (
            <span
              key={stepId}
              className={[
                'pedidosCargaMobilePage__stepChip',
                isActive ? 'pedidosCargaMobilePage__stepChip--active' : '',
                isDone ? 'pedidosCargaMobilePage__stepChip--done' : '',
              ]
                .filter(Boolean)
                .join(' ')}
            >
              {t(stepLabelKeys[stepId])}
            </span>
          );
        })}
      </div>

      {carga.articulosStockPrecargaPendiente ? <PedidosCargaArticulosStockLoadPanel visible /> : null}

      {carga.step === 'cliente' ? (
        <div className="pedidosCargaMobilePage__panel" data-testid="carga-mobile-step-cliente">
          <h2 className="pedidosCargaMobilePage__panelTitle">{t('pedidos.carga.mobile.stepCliente')}</h2>
          {carga.isClienteProfile ? (
            <p className="pedidosCargaMobilePage__readonlyField">
              <span className="pedidosCargaMobilePage__readonlyLabel">
                {t('pedidos.carga.cabecera.cliente')}
              </span>
              {carga.clienteLabel}
            </p>
          ) : (
            <SelectBoxDx
              label={t('pedidos.carga.cabecera.cliente')}
              dataSource={carga.clientesOrdenados}
              value={carga.selectedCliente}
              valueExpr="codCliente"
              displayExpr={(item) => (item ? `${item.codCliente} — ${item.razonSocial ?? item.nombre}` : '')}
              searchEnabled
              disabled={carga.readOnly || carga.clientesLoading || carga.articulosStockPrecargaPendiente}
              inputAttr={{ 'data-testid': 'carga-mobile-cliente' }}
              onValueChanged={(event) => {
                if (!isDevExtremeUserChange(event)) {
                  return;
                }

                void carga.handleClienteChange(event.value ? String(event.value) : null);
              }}
            />
          )}
        </div>
      ) : null}

      {carga.step === 'cabecera' ? <PedidosCargaMobileCabeceraStep carga={carga} /> : null}

      {carga.step === 'articulos' ? (
        <div className="pedidosCargaMobilePage__panel" data-testid="carga-mobile-step-articulos">
          <h2 className="pedidosCargaMobilePage__panelTitle">{t('pedidos.carga.articulosTitle')}</h2>

          {!carga.readOnly ? (
            <div className="pedidosCargaMobilePage__articuloRow">
              <SelectBox
                label={t('pedidos.carga.articuloPlaceholder')}
                dataSource={carga.articulosOrdenados}
                value={carga.articuloSeleccionado}
                valueExpr="codArticulo"
                displayExpr={carga.articuloDisplayExpr}
                searchEnabled
                disabled={carga.articulosPreciosLoading || !carga.cabecera}
                inputAttr={{ 'data-testid': 'carga-mobile-articulo' }}
                onValueChanged={(event) => {
                  if (!isDevExtremeUserChange(event)) {
                    return;
                  }

                  carga.setArticuloSeleccionado(event.value ? String(event.value) : null);
                }}
              />
              <Button
                text={t('pedidos.carga.agregarArticulo')}
                type="default"
                disabled={!carga.articuloSeleccionado || carga.isLoading}
                elementAttr={{ 'data-testid': 'carga-mobile-agregar-articulo' }}
                onClick={carga.handleAgregarArticulo}
              />
            </div>
          ) : null}

          {carga.renglonesVisibles.map((renglon) => (
            <article
              key={renglon.renglon}
              className="pedidosCargaMobilePage__renglonCard"
              data-testid={`carga-mobile-renglon-${renglon.renglon}`}
            >
              <div className="pedidosCargaMobilePage__renglonTitle">
                {renglon.codArticulo} — {renglon.descripcionArticulo}
              </div>
              <div className="pedidosCargaMobilePage__renglonMetrics">
                <span>
                  {t('pedidos.carga.grid.cantidad')}: {renglon.cantidad}
                </span>
                <span>
                  {t('pedidos.carga.grid.precio')}: {formatImporteMoneda(monedaSimbolo, renglon.precio)}
                </span>
                <span>
                  {t('pedidos.carga.renglon.importeNetoConIva')}:{' '}
                  {formatImporteMoneda(
                    monedaSimbolo,
                    calcularImporteNetoConIvaRenglon(renglon, carga.bonificacionNetaCabecera),
                  )}
                </span>
              </div>
              {!carga.readOnly ? (
                <div className="pedidosCargaMobilePage__renglonActions">
                  <Button
                    text={t('pedidos.carga.renglon.editarTitulo')}
                    stylingMode="outlined"
                    elementAttr={{ 'data-testid': `carga-mobile-editar-renglon-${renglon.renglon}` }}
                    onClick={() => {
                      carga.abrirEdicionRenglon(renglon);
                    }}
                  />
                  <Button
                    text={t('grid.action.delete')}
                    stylingMode="text"
                    elementAttr={{ 'data-testid': `carga-mobile-eliminar-renglon-${renglon.renglon}` }}
                    onClick={() => {
                      carga.handleEliminarRenglon(renglon.renglon);
                    }}
                  />
                </div>
              ) : null}
            </article>
          ))}
        </div>
      ) : null}

      {carga.step === 'confirmar' ? (
        <div className="pedidosCargaMobilePage__panel" data-testid="carga-mobile-step-confirmar">
          <h2 className="pedidosCargaMobilePage__panelTitle">{t('pedidos.carga.mobile.stepConfirmar')}</h2>

          <div className="pedidosCargaMobilePage__totales">
            <p>
              <span>{t('pedidos.carga.subtotal')}</span>
              <span>{formatImporteMoneda(monedaSimbolo, carga.totales.subtotal)}</span>
            </p>
            <p>
              <span>{t('pedidos.carga.iva')}</span>
              <span>{formatImporteMoneda(monedaSimbolo, carga.totales.iva)}</span>
            </p>
            <p className="pedidosCargaMobilePage__totalesTotal">
              <span>{t('pedidos.carga.total')}</span>
              <span>{formatImporteMoneda(monedaSimbolo, carga.totales.total)}</span>
            </p>
          </div>

          {!carga.readOnly ? (
            <div className="pedidosCargaMobilePage__grabarActions">
              {carga.showGrabarPresupuesto ? (
                <Button
                  text={t('pedidos.carga.grabarPresupuesto')}
                  stylingMode="outlined"
                  disabled={carga.isLoading}
                  elementAttr={{ 'data-testid': 'carga-mobile-grabar-presupuesto' }}
                  onClick={() => {
                    void carga.saveComprobante('presupuesto');
                  }}
                />
              ) : null}
              {carga.showGrabarPedido ? (
                <Button
                  text={t('pedidos.carga.grabarPedido')}
                  type="default"
                  disabled={carga.isLoading}
                  elementAttr={{ 'data-testid': 'carga-mobile-grabar-pedido' }}
                  onClick={() => {
                    void carga.saveComprobante('pedido');
                  }}
                />
              ) : null}
            </div>
          ) : null}
        </div>
      ) : null}

      {carga.saveError ? (
        <p className="pedidosCargaMobilePage__error" data-testid="carga-mobile-save-error">
          {carga.saveError}
        </p>
      ) : null}

      <div className="pedidosCargaMobilePage__nav">
        {carga.step !== 'cliente' ? (
          <Button
            text={t('pedidos.carga.mobile.anterior')}
            stylingMode="outlined"
            elementAttr={{ 'data-testid': 'carga-mobile-btn-prev' }}
            onClick={carga.goToPreviousStep}
          />
        ) : (
          <div data-testid="btn-cancelar-carga-mobile">
            <Button
              text={t('pedidos.carga.cancelar')}
              stylingMode="text"
              disabled={carga.isLoading}
              onClick={() => {
                void carga.handleCancelar();
              }}
            />
          </div>
        )}

        <div className="pedidosCargaMobilePage__navRight">
          {carga.step !== 'confirmar' ? (
            <Button
              text={t('pedidos.carga.mobile.siguiente')}
              type="default"
              disabled={!carga.canAdvanceFromStep(carga.step) || carga.isLoading}
              elementAttr={{ 'data-testid': 'carga-mobile-btn-next' }}
              onClick={carga.goToNextStep}
            />
          ) : null}
        </div>
      </div>

      <CargaAsistenteIaPanel
        buildDraftContext={buildAsistenteDraftContext}
        readOnly={carga.readOnly}
        onSelectCliente={async (codCliente) => {
          await carga.handleClienteChange(codCliente);
        }}
        onClearDraft={() => {
          void carga.handleClienteChange(null);
        }}
        onAddRenglon={handleAsistenteAddRenglon}
        onUpdateRenglon={handleAsistenteUpdateRenglon}
        onRemoveRenglon={handleAsistenteRemoveRenglon}
        onUpdateCabeceraField={handleAsistenteUpdateCabeceraField}
        onPatchCabeceraFields={handleAsistentePatchCabeceraFields}
        onGrabarPedido={() => {
          void carga.saveComprobante('pedido');
        }}
        onGrabarPresupuesto={() => {
          void carga.saveComprobante('presupuesto');
        }}
        onApplyImageExtract={handleAsistenteApplyImageExtract}
      />

      <PedidosCargaRenglonEditDialog
        visible={carga.editDialogVisible}
        renglon={carga.renglonEnEdicion}
        readOnly={carga.readOnly}
        modificaPrecio={carga.modificaPrecio}
        modificaBonArt={carga.modificaBonArt}
        bonificacionNetaCabecera={carga.bonificacionNetaCabecera}
        monedaSimbolo={monedaSimbolo}
        onClose={carga.cerrarEdicionRenglon}
        onSave={carga.handleGuardarRenglon}
      />

      <PedidosCargaConfirmacionDialog
        visible={carga.confirmacionVisible}
        message={carga.successMessage}
        onClose={() => {
          carga.setConfirmacionVisible(false);
          carga.handlePostGrabacion();
        }}
      />

      <PedidosCargaErroresGrabacionDialog
        visible={carga.erroresGrabacionVisible}
        messages={carga.erroresGrabacionMessages}
        titleKey={
          carga.erroresDialogContext === 'copia'
            ? 'pedidos.carga.erroresCopiaTitulo'
            : 'pedidos.carga.erroresGrabacionTitulo'
        }
        introKey={
          carga.erroresDialogContext === 'copia'
            ? 'pedidos.carga.erroresCopiaIntro'
            : 'pedidos.carga.erroresGrabacionIntro'
        }
        testId={
          carga.erroresDialogContext === 'copia'
            ? 'dialog-errores-copia'
            : 'dialog-errores-grabacion'
        }
        onClose={carga.handleErroresDialogClose}
      />
    </section>
  );
}
