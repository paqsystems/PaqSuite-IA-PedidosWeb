import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import Toast from 'devextreme-react/toast';
import DataGrid, { Column, Editing } from 'devextreme-react/data-grid';
import type { RowUpdatedEvent } from 'devextreme/ui/data_grid';
import { useRequiredSessionContext } from '../../auth/AuthProvider';
import {
  cancelarEdicionPedido,
  fetchClientes,
  fetchComprobante,
  fetchParametrosCarga,
  grabarComprobante,
  iniciarEdicionPedido,
  searchArticulos,
  type ArticuloOption,
  type ClienteOption,
  type ComprobanteRenglon,
  type ParametrosCarga,
} from '../api/comprobanteApi';

const gridId = 'grid-renglones-carga';

function createEmptyRenglon(renglon: number): ComprobanteRenglon {
  return {
    renglon,
    codArticulo: '',
    descripcionArticulo: '',
    cantidad: 1,
    precio: 0,
    porcBonif: 0,
    porcIva: 21,
  };
}

function nextRenglonNumber(renglones: ComprobanteRenglon[]): number {
  return renglones.reduce((max, renglon) => Math.max(max, renglon.renglon), 0) + 1;
}

export function PedidosCargaPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const sessionContext = useRequiredSessionContext();
  const { t } = useTranslation();
  const edicionIniciadaRef = useRef(false);
  const codPedidoEdicionRef = useRef<string | null>(null);

  const [clientes, setClientes] = useState<ClienteOption[]>([]);
  const [selectedCliente, setSelectedCliente] = useState<string | null>(null);
  const [parametrosCarga, setParametrosCarga] = useState<ParametrosCarga | null>(null);
  const [articulos, setArticulos] = useState<ArticuloOption[]>([]);
  const [articuloSeleccionado, setArticuloSeleccionado] = useState<string | null>(null);
  const [codPedidoActual, setCodPedidoActual] = useState<string | null>(null);
  const [estadoActual, setEstadoActual] = useState<number | null>(null);
  const [codPedidoOrigen, setCodPedidoOrigen] = useState<string | null>(null);
  const [codPresupuestoOrigen, setCodPresupuestoOrigen] = useState<string | null>(null);
  const [codComprobanteOrigenCopia, setCodComprobanteOrigenCopia] = useState<string | null>(null);
  const [renglones, setRenglones] = useState<ComprobanteRenglon[]>([createEmptyRenglon(1)]);
  const [isLoading, setIsLoading] = useState(false);
  const [mailToastVisible, setMailToastVisible] = useState(false);
  const [pendingMailToast, setPendingMailToast] = useState(false);
  const [successToastVisible, setSuccessToastVisible] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [confirmacionVisible, setConfirmacionVisible] = useState(false);
  const [mailAvisoVisible, setMailAvisoVisible] = useState(false);

  const modo = searchParams.get('modo') ?? 'nuevo';
  const comprobanteId = searchParams.get('codComprobante') ?? searchParams.get('id');
  const readOnly = modo === 'ver';
  const isClienteProfile =
    sessionContext.functionalProfile === 'cliente' || sessionContext.codCliente !== null;

  const tipoComprobanteLabel =
    estadoActual === 99 || searchParams.get('tipoOrigen') === 'presupuesto'
      ? t('pedidos.carga.tipoPresupuesto')
      : t('pedidos.carga.tipoPedido');

  const showGrabarPedido = useMemo(() => {
    if (readOnly) {
      return false;
    }

    if (modo === 'convertir') {
      return true;
    }

    return modo === 'nuevo' || modo === 'copia' || modo === 'editar' || estadoActual === 99;
  }, [estadoActual, modo, readOnly]);

  const showGrabarPresupuesto = useMemo(() => {
    if (readOnly || modo === 'convertir') {
      return false;
    }

    return (
      modo === 'nuevo' ||
      modo === 'copia' ||
      modo === 'editar' ||
      estadoActual === 0 ||
      estadoActual === -1 ||
      estadoActual === 99
    );
  }, [estadoActual, modo, readOnly]);

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      try {
        const parametros = await fetchParametrosCarga();
        if (!mounted) {
          return;
        }

        setParametrosCarga(parametros);
      } catch {
        if (mounted) {
          setParametrosCarga(null);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, []);

  useEffect(() => {
    if (isClienteProfile) {
      setSelectedCliente(sessionContext.codCliente);
      return;
    }

    let mounted = true;

    const load = async () => {
      try {
        const data = await fetchClientes();
        if (!mounted) {
          return;
        }

        setClientes(data);
        setSelectedCliente((previousValue) => previousValue ?? data[0]?.codCliente ?? null);
      } catch {
        if (!mounted) {
          return;
        }

        setClientes([]);
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [isClienteProfile, sessionContext.codCliente]);

  useEffect(() => {
    let mounted = true;

    const loadArticulos = async () => {
      try {
        const items = await searchArticulos('');
        if (mounted) {
          setArticulos(items);
        }
      } catch {
        if (mounted) {
          setArticulos([]);
        }
      }
    };

    void loadArticulos();

    return () => {
      mounted = false;
    };
  }, []);

  useEffect(() => {
    if (!comprobanteId) {
      setCodComprobanteOrigenCopia(null);
      return;
    }

    if (modo === 'copia') {
      setCodComprobanteOrigenCopia(comprobanteId);
    }

    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      try {
        const comprobante = await fetchComprobante(comprobanteId);
        if (!mounted) {
          return;
        }

        const esCopiaOConversion = modo === 'copia' || modo === 'convertir';
        setCodPedidoActual(esCopiaOConversion ? null : comprobante.codPedido);
        setEstadoActual(comprobante.estado);
        setSelectedCliente(comprobante.codCliente ?? sessionContext.codCliente ?? null);
        setRenglones(
          comprobante.renglones.length > 0 ? comprobante.renglones : [createEmptyRenglon(1)],
        );

        if (comprobante.estado === 99) {
          setCodPresupuestoOrigen(esCopiaOConversion ? comprobante.codPedido : comprobante.codPedido);
        } else if (comprobante.estado === 0 || comprobante.estado === -1) {
          setCodPedidoOrigen(esCopiaOConversion ? comprobante.codPedido : comprobante.codPedido);
        }

        if (modo === 'editar' && comprobante.estado === 0) {
          await iniciarEdicionPedido(comprobante.codPedido);
          if (mounted) {
            edicionIniciadaRef.current = true;
            codPedidoEdicionRef.current = comprobante.codPedido;
            setEstadoActual(-1);
          }
        }
      } catch {
        if (mounted) {
          setRenglones([createEmptyRenglon(1)]);
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [comprobanteId, modo, sessionContext.codCliente]);

  useEffect(
    () => () => {
      const codPedido = codPedidoEdicionRef.current;
      if (edicionIniciadaRef.current && codPedido) {
        void cancelarEdicionPedido(codPedido).catch(() => undefined);
      }
    },
    [],
  );

  const handleCancelar = useCallback(async () => {
    const codPedido = codPedidoEdicionRef.current;
    if (edicionIniciadaRef.current && codPedido) {
      try {
        await cancelarEdicionPedido(codPedido);
      } catch {
        // Navegación prioritaria; el backend puede limpiar bloqueo por timeout.
      }
      edicionIniciadaRef.current = false;
      codPedidoEdicionRef.current = null;
    }

    navigate(-1);
  }, [navigate]);

  const handleAgregarArticulo = useCallback(() => {
    if (readOnly || !articuloSeleccionado) {
      return;
    }

    const articulo = articulos.find((item) => item.codArticulo === articuloSeleccionado);
    if (!articulo) {
      return;
    }

    setRenglones((previousRenglones) => [
      ...previousRenglones,
      {
        renglon: nextRenglonNumber(previousRenglones),
        codArticulo: articulo.codArticulo,
        descripcionArticulo: articulo.descripcion,
        cantidad: 1,
        precio: 0,
        porcBonif: articulo.bonificacion,
        porcIva: articulo.porcIva,
      },
    ]);
    setArticuloSeleccionado(null);
  }, [articuloSeleccionado, articulos, readOnly]);

  const handleRowUpdated = useCallback((event: RowUpdatedEvent<ComprobanteRenglon, number>) => {
    const updatedRow = event.data;
    if (!updatedRow) {
      return;
    }

    setRenglones((previousRenglones) =>
      previousRenglones.map((renglon) =>
        renglon.renglon === updatedRow.renglon ? { ...updatedRow } : renglon,
      ),
    );
  }, []);

  const subtotal = useMemo(
    () =>
      renglones.reduce((total, renglon) => {
        const neto = renglon.precio * (1 - renglon.porcBonif / 100);
        return total + renglon.cantidad * neto;
      }, 0),
    [renglones],
  );
  const iva = useMemo(
    () =>
      renglones.reduce((total, renglon) => {
        const neto = renglon.precio * (1 - renglon.porcBonif / 100) * renglon.cantidad;
        return total + neto * (renglon.porcIva / 100);
      }, 0),
    [renglones],
  );
  const total = subtotal + iva;
  const modificaPrecio = parametrosCarga?.modificaPrecio ?? true;
  const modificaBonArt = parametrosCarga?.modificaBonArt ?? true;

  const saveComprobante = async (accionGrabacion: 'pedido' | 'presupuesto') => {
    if (!selectedCliente || readOnly) {
      return;
    }

    setIsLoading(true);
    try {
      const codPedidoParaGrabar =
        accionGrabacion === 'pedido' && estadoActual === 99 ? null : codPedidoActual;

      const codPresupuestoParaConversion =
        accionGrabacion === 'pedido' && (estadoActual === 99 || modo === 'convertir')
          ? (codPresupuestoOrigen ?? comprobanteId ?? codPedidoActual)
          : codPresupuestoOrigen;

      const codPedidoParaConversion =
        accionGrabacion === 'presupuesto' && (estadoActual === 0 || estadoActual === -1)
          ? (codPedidoOrigen ?? codPedidoActual)
          : codPedidoOrigen;

      const response = await grabarComprobante({
        accionGrabacion,
        codPedido: codPedidoParaGrabar,
        codPedidoOrigen:
          accionGrabacion === 'presupuesto' && estadoActual !== 99 ? codPedidoParaConversion : null,
        codPresupuestoOrigen:
          accionGrabacion === 'pedido' && (estadoActual === 99 || modo === 'convertir')
            ? codPresupuestoParaConversion
            : null,
        codComprobanteOrigenCopia: modo === 'copia' ? codComprobanteOrigenCopia : null,
        codCliente: selectedCliente,
        renglones,
      });

      const resultado = response.resultado;
      const nroVisible = resultado.nro_visible ?? 0;
      const guidSufijo = resultado.guidSufijo ?? '';

      edicionIniciadaRef.current = false;
      codPedidoEdicionRef.current = null;

      setSuccessMessage(
        t('pedidos.carga.grabacionExitosa', {
          numero: nroVisible,
          guid: guidSufijo,
        }),
      );
      setConfirmacionVisible(true);
      setSuccessToastVisible(true);

      if (resultado.cod_pedido) {
        setCodPedidoActual(resultado.cod_pedido);
      }

      if (resultado.mailEnviado === false) {
        setPendingMailToast(true);
        setMailAvisoVisible(true);
        window.setTimeout(() => {
          setMailToastVisible(true);
        }, 500);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <section data-testid="page-pedidos-carga">
      <h2>{t('pages.pedidosCarga')}</h2>
      <p data-testid="label-tipo-comprobante">{tipoComprobanteLabel}</p>
      {readOnly ? (
        <p data-testid="label-modo-solo-lectura">{t('pedidos.carga.modoSoloLectura')}</p>
      ) : null}
      <div className="pedidosCargaPage__toolbar">
        {showGrabarPedido ? (
          <div data-testid="btn-grabar-pedido">
            <Button
              text={t('pedidos.carga.grabarPedido')}
              type="default"
              stylingMode="contained"
              disabled={isLoading}
              onClick={() => {
                void saveComprobante('pedido');
              }}
            />
          </div>
        ) : null}
        {showGrabarPresupuesto ? (
          <div data-testid="btn-grabar-presupuesto">
            <Button
              text={t('pedidos.carga.grabarPresupuesto')}
              type="default"
              stylingMode="outlined"
              disabled={isLoading}
              onClick={() => {
                void saveComprobante('presupuesto');
              }}
            />
          </div>
        ) : null}
        <div data-testid="btn-cancelar-carga">
          <Button
            text={t('pedidos.carga.cancelar')}
            stylingMode="text"
            onClick={() => {
              void handleCancelar();
            }}
          />
        </div>
      </div>

      <section data-testid="form-cabecera-carga">
        <h3>{t('pedidos.carga.cabeceraTitle')}</h3>
        {selectedCliente ? <span data-testid="cliente-cargado" hidden aria-hidden="true" /> : null}
        {isClienteProfile ? (
          <p data-testid="cliente-fijo">{selectedCliente ?? sessionContext.codCliente}</p>
        ) : (
          <SelectBox
            dataSource={clientes}
            valueExpr="codCliente"
            displayExpr="nombre"
            value={selectedCliente}
            readOnly={readOnly}
            onValueChanged={(event) => {
              setSelectedCliente((event.value as string | null) ?? null);
            }}
            placeholder={t('pedidos.carga.clientePlaceholder')}
            inputAttr={{ 'data-testid': 'cliente-select' }}
          />
        )}
      </section>

      {!readOnly ? (
        <section data-testid="form-articulo-carga">
          <SelectBox
            dataSource={articulos}
            valueExpr="codArticulo"
            displayExpr="descripcion"
            value={articuloSeleccionado}
            searchEnabled={true}
            searchExpr={['codArticulo', 'descripcion']}
            onValueChanged={(event) => {
              setArticuloSeleccionado((event.value as string | null) ?? null);
            }}
            placeholder={t('pedidos.carga.articuloPlaceholder')}
            inputAttr={{ 'data-testid': 'articulo-select' }}
          />
          <div data-testid="btn-agregar-articulo">
            <Button
              text={t('pedidos.carga.agregarArticulo')}
              stylingMode="outlined"
              onClick={handleAgregarArticulo}
            />
          </div>
        </section>
      ) : null}

      <div data-testid={gridId}>
        <DataGrid
          dataSource={renglones}
          keyExpr="renglon"
          showBorders={true}
          disabled={isLoading}
          onRowUpdated={handleRowUpdated}
        >
          <Editing mode="cell" allowUpdating={!readOnly} allowAdding={false} allowDeleting={false} />
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
            allowEditing={!readOnly}
          />
          <Column
            dataField="precio"
            caption={t('pedidos.carga.grid.precio')}
            dataType="number"
            format="currency"
            allowEditing={!readOnly && modificaPrecio}
            cssClass="renglon-precio"
          />
          <Column
            dataField="porcBonif"
            caption={t('pedidos.carga.grid.bonificacion')}
            dataType="number"
            format="#0.##'%'"
            allowEditing={!readOnly && modificaBonArt}
            cssClass="renglon-bonificacion"
          />
        </DataGrid>
      </div>

      <section data-testid="totales-carga">
        <h3>{t('pedidos.carga.totalesTitle')}</h3>
        {confirmacionVisible ? (
          <p data-testid="confirmacion-grabacion">{successMessage}</p>
        ) : null}
        {mailAvisoVisible ? (
          <p data-testid="aviso-mail-envio-fallido" role="status">
            {t('pedidos.carga.mailEnvioFallido')}
          </p>
        ) : null}
        <p>
          {t('pedidos.carga.subtotal')}: {subtotal.toFixed(2)}
        </p>
        <p>
          {t('pedidos.carga.iva')}: {iva.toFixed(2)}
        </p>
        <p>
          {t('pedidos.carga.total')}: {total.toFixed(2)}
        </p>
      </section>

      <Toast
        visible={successToastVisible}
        message={successMessage}
        type="success"
        displayTime={5000}
        onHiding={() => {
          setSuccessToastVisible(false);
          if (pendingMailToast) {
            setMailToastVisible(true);
            setPendingMailToast(false);
          }
        }}
        elementAttr={{ 'data-testid': 'toast-grabacion-exitosa' }}
      />
      <Toast
        visible={mailToastVisible}
        message={t('pedidos.carga.mailEnvioFallido')}
        type="warning"
        displayTime={4000}
        onHiding={() => setMailToastVisible(false)}
        elementAttr={{ 'data-testid': 'toast-mail-envio-fallido' }}
      />
    </section>
  );
}
