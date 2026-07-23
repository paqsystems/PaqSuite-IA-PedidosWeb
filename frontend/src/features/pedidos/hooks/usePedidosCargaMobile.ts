import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useRequiredSessionContext } from '../../auth/AuthProvider';
import {
  cancelarEdicionPedido,
  copiarComprobante,
  fetchArticulosPreciosCatalogoCarga,
  fetchArticulosStockCatalogoCarga,
  fetchCabeceraInicial,
  fetchClientes,
  fetchComprobante,
  fetchParametrosCarga,
  grabarComprobante,
  iniciarEdicionPedido,
  type ArticuloOption,
  type ClienteOption,
  type ComprobanteRenglon,
  type ParametrosCarga,
} from '../api/comprobanteApi';
import {
  emptyComprobanteCabecera,
  type CabeceraCatalogos,
  type ComprobanteCabecera,
} from '../types/comprobanteCabecera';
import {
  etiquetaArticulo,
  etiquetaCliente,
  ordenarArticulosPorDescripcion,
  ordenarClientes,
} from '../utils/cargaCatalogos';
import { actualizarPreciosRenglonesPorLista } from '../utils/actualizarPreciosRenglones';
import { mergeArticulosStockPrecios } from '../utils/mergeArticulosStockPrecios';
import {
  calcularBonificacionNeta,
  calcularTotalesComprobante,
  createEmptyRenglon,
  nextRenglonNumber,
  normalizarPorcIvaAlmacenado,
  renglonesValidosParaGrabar,
} from '../utils/renglonesCarga';
import { resolveGrabacionErrorMessages } from '../utils/resolveGrabacionErrorMessages';

const emptyCatalogos: CabeceraCatalogos = {
  condicionesVenta: [],
  transportes: [],
  listasPrecios: [],
  direccionesEntrega: [],
  perfiles: [],
};

export type PedidosCargaMobileStep = 'cliente' | 'cabecera' | 'articulos' | 'confirmar';

const stepOrder: PedidosCargaMobileStep[] = ['cliente', 'cabecera', 'articulos', 'confirmar'];

export function usePedidosCargaMobile() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const sessionContext = useRequiredSessionContext();
  const { t } = useTranslation();

  const edicionIniciadaRef = useRef(false);
  const codPedidoEdicionRef = useRef<string | null>(null);
  const ultimaAccionGrabacionRef = useRef<'pedido' | 'presupuesto' | null>(null);
  const isHydratingComprobanteRef = useRef(false);
  const articulosStockLoadRef = useRef<Promise<void> | null>(null);
  const articulosPreciosLoadRef = useRef<Promise<void> | null>(null);
  const articulosListaPreciosRef = useRef<number | null>(null);
  const renglonesRef = useRef<ComprobanteRenglon[]>([createEmptyRenglon(1)]);
  const listaPreciosAnteriorRef = useRef<number | null>(null);

  const [step, setStep] = useState<PedidosCargaMobileStep>('cliente');
  const [clientes, setClientes] = useState<ClienteOption[]>([]);
  const [clientesLoading, setClientesLoading] = useState(false);
  const [selectedCliente, setSelectedCliente] = useState<string | null>(null);
  const [cabecera, setCabecera] = useState<ComprobanteCabecera | null>(null);
  const [catalogos, setCatalogos] = useState<CabeceraCatalogos>(emptyCatalogos);
  const [parametrosCarga, setParametrosCarga] = useState<ParametrosCarga | null>(null);
  const [articuloSeleccionado, setArticuloSeleccionado] = useState<string | null>(null);
  const [articulosStock, setArticulosStock] = useState<ArticuloOption[]>([]);
  const [articulosPrecios, setArticulosPrecios] = useState<ArticuloOption[]>([]);
  const [articulosStockLoading, setArticulosStockLoading] = useState(false);
  const [articulosPreciosLoading, setArticulosPreciosLoading] = useState(false);
  const [, setArticulosPreciosListaCargada] = useState<number | null>(null);
  const [codPedidoActual, setCodPedidoActual] = useState<string | null>(null);
  const [estadoActual, setEstadoActual] = useState<number | null>(null);
  const [codPedidoOrigen, setCodPedidoOrigen] = useState<string | null>(null);
  const [codPresupuestoOrigen, setCodPresupuestoOrigen] = useState<string | null>(null);
  const [codComprobanteOrigenCopia, setCodComprobanteOrigenCopia] = useState<string | null>(null);
  const [renglones, setRenglones] = useState<ComprobanteRenglon[]>([createEmptyRenglon(1)]);
  renglonesRef.current = renglones;

  const [isLoading, setIsLoading] = useState(false);
  const [cabeceraLoading, setCabeceraLoading] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState('');
  const [confirmacionVisible, setConfirmacionVisible] = useState(false);
  const [erroresGrabacionVisible, setErroresGrabacionVisible] = useState(false);
  const [erroresGrabacionMessages, setErroresGrabacionMessages] = useState<string[]>([]);
  const [erroresDialogContext, setErroresDialogContext] = useState<'grabacion' | 'copia'>('grabacion');
  const [autoOpenRenglonId, setAutoOpenRenglonId] = useState<number | null>(null);
  const [renglonEnEdicion, setRenglonEnEdicion] = useState<ComprobanteRenglon | null>(null);
  const [editDialogVisible, setEditDialogVisible] = useState(false);

  const modo = searchParams.get('modo') ?? 'nuevo';
  const comprobanteId = searchParams.get('codComprobante') ?? searchParams.get('id');
  const readOnly = modo === 'ver';
  const isClienteProfile =
    sessionContext.functionalProfile === 'cliente' || sessionContext.codCliente !== null;

  const clientesOrdenados = useMemo(() => ordenarClientes(clientes), [clientes]);

  const articulosOrdenados = useMemo(
    () => ordenarArticulosPorDescripcion(mergeArticulosStockPrecios(articulosStock, articulosPrecios)),
    [articulosPrecios, articulosStock],
  );

  const articuloSeleccionadoData = useMemo(() => {
    if (!articuloSeleccionado) {
      return null;
    }

    return articulosOrdenados.find((item) => item.codArticulo === articuloSeleccionado) ?? null;
  }, [articuloSeleccionado, articulosOrdenados]);

  const articulosStockPrecargaPendiente = articulosStockLoading && articulosStock.length === 0;

  const bonificacionNetaCabecera = useMemo(() => {
    if (!cabecera) {
      return 0;
    }

    return calcularBonificacionNeta(cabecera.bonif1, cabecera.bonif2, cabecera.bonif3);
  }, [cabecera]);

  const totales = useMemo(
    () => calcularTotalesComprobante(renglones, bonificacionNetaCabecera),
    [bonificacionNetaCabecera, renglones],
  );

  const renglonesVisibles = useMemo(() => renglonesValidosParaGrabar(renglones), [renglones]);

  const modificaPrecio = parametrosCarga?.modificaPrecio ?? true;
  const modificaBonArt = parametrosCarga?.modificaBonArt ?? true;
  const modificaListaPrec = parametrosCarga?.modificaListaPrec ?? true;

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

  const loadCabeceraForCliente = useCallback(async (codCliente: string) => {
    setCabeceraLoading(true);
    try {
      const result = await fetchCabeceraInicial(codCliente);
      setCabecera(result.cabecera);
      setCatalogos(result.catalogos);
    } catch {
      setCabecera(emptyComprobanteCabecera(codCliente));
      setCatalogos(emptyCatalogos);
    } finally {
      setCabeceraLoading(false);
    }
  }, []);

  const loadArticulosStock = useCallback(async () => {
    if (articulosStockLoadRef.current) {
      return articulosStockLoadRef.current;
    }

    let loadPromise!: Promise<void>;
    loadPromise = (async () => {
      setArticulosStockLoading(true);

      try {
        const data = await fetchArticulosStockCatalogoCarga();
        if (articulosStockLoadRef.current === loadPromise) {
          setArticulosStock(data);
        }
      } catch {
        if (articulosStockLoadRef.current === loadPromise) {
          setArticulosStock([]);
        }
      } finally {
        if (articulosStockLoadRef.current === loadPromise) {
          setArticulosStockLoading(false);
          articulosStockLoadRef.current = null;
        }
      }
    })();

    articulosStockLoadRef.current = loadPromise;
    return loadPromise;
  }, []);

  const loadArticulosPrecios = useCallback(async (codListaNum: number) => {
    if (
      articulosPreciosLoadRef.current &&
      articulosListaPreciosRef.current === codListaNum
    ) {
      return articulosPreciosLoadRef.current;
    }

    let loadPromise!: Promise<void>;
    loadPromise = (async () => {
      setArticulosPreciosLoading(true);
      setArticulosPreciosListaCargada(null);

      try {
        const data = await fetchArticulosPreciosCatalogoCarga(codListaNum);
        if (articulosPreciosLoadRef.current === loadPromise) {
          setArticulosPrecios(data);
          articulosListaPreciosRef.current = codListaNum;
          setArticulosPreciosListaCargada(codListaNum);
        }
      } catch {
        if (articulosPreciosLoadRef.current === loadPromise) {
          setArticulosPrecios([]);
          articulosListaPreciosRef.current = null;
          setArticulosPreciosListaCargada(null);
        }
      } finally {
        if (articulosPreciosLoadRef.current === loadPromise) {
          setArticulosPreciosLoading(false);
          articulosPreciosLoadRef.current = null;
        }
      }
    })();

    articulosPreciosLoadRef.current = loadPromise;
    return loadPromise;
  }, []);

  useEffect(() => {
    let mounted = true;

    void fetchParametrosCarga()
      .then((parametros) => {
        if (mounted) {
          setParametrosCarga(parametros);
        }
      })
      .catch(() => {
        if (mounted) {
          setParametrosCarga(null);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

  useEffect(() => {
    if (isClienteProfile) {
      const codCliente = sessionContext.codCliente ?? '';
      setSelectedCliente(codCliente);
      if (codCliente && modo === 'nuevo' && !comprobanteId) {
        void loadCabeceraForCliente(codCliente);
      }
      return;
    }

    let mounted = true;

    const load = async () => {
      setClientesLoading(true);

      try {
        const data = await fetchClientes(sessionContext.user.id);
        if (mounted) {
          setClientes(data);
        }
      } catch {
        if (mounted) {
          setClientes([]);
          setSaveError(t('pedidos.carga.errorCargaClientes'));
        }
      } finally {
        if (mounted) {
          setClientesLoading(false);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [comprobanteId, isClienteProfile, loadCabeceraForCliente, modo, sessionContext.codCliente, sessionContext.user.id, t]);

  useEffect(() => {
    void loadArticulosStock();
  }, [loadArticulosStock]);

  useEffect(() => {
    const codListaRaw = cabecera?.listaPrecios ?? null;
    const codLista = codListaRaw !== null ? Number(codListaRaw) : null;

    if (codLista === null || Number.isNaN(codLista) || codLista <= 0 || readOnly) {
      setArticulosPrecios([]);
      setArticulosPreciosListaCargada(null);
      articulosListaPreciosRef.current = null;
      return;
    }

    void loadArticulosPrecios(codLista);
  }, [cabecera?.listaPrecios, loadArticulosPrecios, readOnly]);

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
      isHydratingComprobanteRef.current = true;

      try {
        if (modo === 'copia') {
          const tipoDestino =
            searchParams.get('tipoOrigen') === 'presupuesto' ? 'presupuesto' : 'pedido';
          const copia = await copiarComprobante(comprobanteId, tipoDestino);
          if (!mounted) {
            return;
          }

          const { catalogos: catalogosInicial } = await fetchCabeceraInicial(copia.cabecera.codCliente);
          if (!mounted) {
            return;
          }

          setCodPedidoActual(null);
          setEstadoActual(tipoDestino === 'presupuesto' ? 99 : 0);
          setCodPedidoOrigen(null);
          setCodPresupuestoOrigen(null);
          setSelectedCliente(copia.cabecera.codCliente ?? sessionContext.codCliente ?? null);
          setCabecera(copia.cabecera);
          setCatalogos(catalogosInicial);
          setRenglones(
            copia.renglones.length > 0 ? copia.renglones : [createEmptyRenglon(1)],
          );
          setStep(copia.cabecera.codCliente ? 'articulos' : 'cliente');
          return;
        }

        const comprobante = await fetchComprobante(comprobanteId);
        if (!mounted) {
          return;
        }

        const esCopiaOConversion = modo === 'copia' || modo === 'convertir';
        setCodPedidoActual(esCopiaOConversion ? null : comprobante.codPedido);
        setEstadoActual(comprobante.estado);
        setSelectedCliente(comprobante.codCliente ?? sessionContext.codCliente ?? null);
        setCabecera(comprobante.cabecera);
        setCatalogos(comprobante.catalogos);
        setRenglones(
          comprobante.renglones.length > 0 ? comprobante.renglones : [createEmptyRenglon(1)],
        );
        setStep(comprobante.codCliente ? 'articulos' : 'cliente');

        if (comprobante.estado === 99) {
          setCodPresupuestoOrigen(comprobante.codPedido);
        } else if (comprobante.estado === 0 || comprobante.estado === -1) {
          setCodPedidoOrigen(comprobante.codPedido);
        }

        if (modo === 'editar' && comprobante.estado === 0) {
          await iniciarEdicionPedido(comprobante.codPedido);
          if (mounted) {
            edicionIniciadaRef.current = true;
            codPedidoEdicionRef.current = comprobante.codPedido;
            setEstadoActual(-1);
          }
        }
      } catch (error) {
        if (mounted) {
          if (modo === 'copia') {
            const messages = resolveGrabacionErrorMessages(error, t);
            setErroresDialogContext('copia');
            setErroresGrabacionMessages(
              messages.length > 0 ? messages : [t('pedidos.carga.errorCargaComprobante')],
            );
            setErroresGrabacionVisible(true);
            setSaveError(null);
          } else {
            setSaveError(t('pedidos.carga.errorCargaComprobante'));
          }
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
          isHydratingComprobanteRef.current = false;
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [comprobanteId, modo, searchParams, sessionContext.codCliente, t]);

  useEffect(
    () => () => {
      const codPedido = codPedidoEdicionRef.current;
      if (edicionIniciadaRef.current && codPedido) {
        void cancelarEdicionPedido(codPedido).catch(() => undefined);
      }
    },
    [],
  );

  useEffect(() => {
    const codListaRaw = cabecera?.listaPrecios ?? null;
    const codLista = codListaRaw !== null ? Number(codListaRaw) : null;

    if (codLista === null || Number.isNaN(codLista) || codLista <= 0 || readOnly) {
      listaPreciosAnteriorRef.current = codLista;
      return;
    }

    const listaAnterior = listaPreciosAnteriorRef.current;
    listaPreciosAnteriorRef.current = codLista;

    if (listaAnterior === null || listaAnterior === codLista) {
      return;
    }

    let cancelled = false;

    void (async () => {
      try {
        const renglonesActualizados = await actualizarPreciosRenglonesPorLista(
          renglonesRef.current,
          codLista,
        );

        if (!cancelled) {
          setRenglones(renglonesActualizados);
        }
      } catch {
        // Mantiene precios previos si falla la consulta.
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [cabecera?.listaPrecios, readOnly]);

  const handleCabeceraChange = useCallback((next: ComprobanteCabecera) => {
    setCabecera({
      ...next,
      descuento: calcularBonificacionNeta(next.bonif1, next.bonif2, next.bonif3),
    });
  }, []);

  const handleClienteChange = useCallback(
    async (codCliente: string | null) => {
      if (isHydratingComprobanteRef.current || readOnly) {
        return;
      }

      if (!codCliente) {
        setSelectedCliente(null);
        setCabecera(null);
        setCatalogos(emptyCatalogos);
        setRenglones([createEmptyRenglon(1)]);
        setStep('cliente');
        return;
      }

      setSelectedCliente(codCliente);
      setRenglones([createEmptyRenglon(1)]);
      setArticuloSeleccionado(null);
      await loadCabeceraForCliente(codCliente);
      setStep('cabecera');
    },
    [loadCabeceraForCliente, readOnly],
  );

  const handleAgregarArticulo = useCallback(() => {
    if (readOnly || !articuloSeleccionado || !articuloSeleccionadoData || !cabecera) {
      return;
    }

    const articulo = articuloSeleccionadoData;
    const yaExiste = renglonesVisibles.some((renglon) => renglon.codArticulo === articulo.codArticulo);
    if (yaExiste) {
      setSaveError(t('pedidos.carga.articuloDuplicado'));
      return;
    }

    setSaveError(null);
    const nuevoRenglon: ComprobanteRenglon = {
      renglon: nextRenglonNumber(renglonesVisibles),
      codArticulo: articulo.codArticulo,
      descripcionArticulo: articulo.descripcion,
      cantidad: 1,
      precio: articulo.precio ?? 0,
      porcBonif: articulo.bonificacion,
      porcIva: normalizarPorcIvaAlmacenado(articulo.porcIva),
    };

    setRenglones([...renglonesVisibles, nuevoRenglon]);
    setAutoOpenRenglonId(nuevoRenglon.renglon);
    setArticuloSeleccionado(null);
  }, [articuloSeleccionado, articuloSeleccionadoData, cabecera, readOnly, renglonesVisibles, t]);

  const handleEliminarRenglon = useCallback((renglonId: number) => {
    setRenglones((current) => current.filter((renglon) => renglon.renglon !== renglonId));
  }, []);

  const handleGuardarRenglon = useCallback((renglonActualizado: ComprobanteRenglon) => {
    setRenglones((current) =>
      current.map((renglon) =>
        renglon.renglon === renglonActualizado.renglon ? { ...renglonActualizado } : renglon,
      ),
    );
  }, []);

  const abrirEdicionRenglon = useCallback((renglon: ComprobanteRenglon) => {
    setRenglonEnEdicion({ ...renglon });
    setEditDialogVisible(true);
  }, []);

  const cerrarEdicionRenglon = useCallback(() => {
    setEditDialogVisible(false);
    setRenglonEnEdicion(null);
    setAutoOpenRenglonId(null);
  }, []);

  useEffect(() => {
    if (autoOpenRenglonId === null) {
      return;
    }

    const renglon = renglonesVisibles.find((item) => item.renglon === autoOpenRenglonId);
    if (renglon) {
      abrirEdicionRenglon(renglon);
    }
  }, [abrirEdicionRenglon, autoOpenRenglonId, renglonesVisibles]);

  const resetParaNuevoComprobante = useCallback(async () => {
    edicionIniciadaRef.current = false;
    codPedidoEdicionRef.current = null;
    ultimaAccionGrabacionRef.current = null;
    listaPreciosAnteriorRef.current = null;

    setCodPedidoActual(null);
    setEstadoActual(null);
    setCodPedidoOrigen(null);
    setCodPresupuestoOrigen(null);
    setCodComprobanteOrigenCopia(null);
    setRenglones([createEmptyRenglon(1)]);
    setArticuloSeleccionado(null);
    setSaveError(null);
    setConfirmacionVisible(false);
    setStep('cliente');

    if (isClienteProfile) {
      const codCliente = sessionContext.codCliente ?? selectedCliente ?? '';
      if (codCliente) {
        setSelectedCliente(codCliente);
        await loadCabeceraForCliente(codCliente);
        setStep('cabecera');
      }
      return;
    }

    setSelectedCliente(null);
    setCabecera(null);
    setCatalogos(emptyCatalogos);

    if (comprobanteId || modo !== 'nuevo') {
      navigate('/pedidos/carga?modo=nuevo', { replace: true });
    }
  }, [
    comprobanteId,
    isClienteProfile,
    loadCabeceraForCliente,
    modo,
    navigate,
    selectedCliente,
    sessionContext.codCliente,
  ]);

  const handleCancelar = useCallback(async () => {
    const codPedido = codPedidoEdicionRef.current;
    if (edicionIniciadaRef.current && codPedido) {
      try {
        await cancelarEdicionPedido(codPedido);
      } catch {
        // Prioriza limpiar pantalla.
      }
    }

    await resetParaNuevoComprobante();
  }, [resetParaNuevoComprobante]);

  const handleErroresDialogClose = useCallback(() => {
    setErroresGrabacionVisible(false);
    if (erroresDialogContext === 'copia') {
      navigate(-1);
    }
  }, [erroresDialogContext, navigate]);

  const handlePostGrabacion = useCallback(() => {
    const accionGrabacion = ultimaAccionGrabacionRef.current;
    ultimaAccionGrabacionRef.current = null;

    if (!accionGrabacion) {
      return;
    }

    const cargaRecurrente = parametrosCarga?.cargaRecurrente ?? true;

    if (cargaRecurrente) {
      void resetParaNuevoComprobante();
      return;
    }

    const destino =
      accionGrabacion === 'presupuesto' ? '/presupuestos/ingresados' : '/pedidos/ingresados';
    navigate(destino);
  }, [navigate, parametrosCarga?.cargaRecurrente, resetParaNuevoComprobante]);

  const saveComprobante = useCallback(
    async (accionGrabacion: 'pedido' | 'presupuesto') => {
      if (!selectedCliente || !cabecera || readOnly) {
        return;
      }

      const renglonesGrabar = renglonesValidosParaGrabar(renglones);
      if (renglonesGrabar.length === 0) {
        setSaveError(t('pedidos.carga.sinRenglones'));
        return;
      }

      setSaveError(null);
      setIsLoading(true);

      try {
        const esEdicionPedido = estadoActual === 0 || estadoActual === -1;
        const esEdicionPresupuesto = estadoActual === 99;
        const esConversionPresupuestoAPedido =
          accionGrabacion === 'pedido' && (estadoActual === 99 || modo === 'convertir');
        const esConversionPedidoAPresupuesto =
          accionGrabacion === 'presupuesto' && esEdicionPedido;

        const codPedidoParaGrabar = (() => {
          if (esConversionPresupuestoAPedido) {
            return null;
          }

          if (accionGrabacion === 'presupuesto') {
            return esEdicionPresupuesto ? codPedidoActual : null;
          }

          return esEdicionPedido ? codPedidoActual : null;
        })();

        const codPresupuestoParaConversion = esConversionPresupuestoAPedido
          ? (codPresupuestoOrigen ?? comprobanteId ?? codPedidoActual)
          : null;

        const codPedidoParaConversion = esConversionPedidoAPresupuesto
          ? (codPedidoOrigen ?? codPedidoActual)
          : null;

        const response = await grabarComprobante({
          accionGrabacion,
          codPedido: codPedidoParaGrabar,
          codPedidoOrigen: codPedidoParaConversion,
          codPresupuestoOrigen: codPresupuestoParaConversion,
          codComprobanteOrigenCopia: modo === 'copia' ? codComprobanteOrigenCopia : null,
          cabecera: {
            ...cabecera,
            codCliente: selectedCliente,
            descuento: bonificacionNetaCabecera,
          },
          renglones: renglonesGrabar,
        });

        const resultado = response.resultado;
        ultimaAccionGrabacionRef.current = accionGrabacion;
        setSuccessMessage(
          t('pedidos.carga.grabacionExitosa', {
            numero: resultado.nro_visible ?? 0,
            guid: resultado.guidSufijo ?? '',
          }),
        );
        setConfirmacionVisible(true);

        if (resultado.cod_pedido) {
          setCodPedidoActual(resultado.cod_pedido);
        }

        if (typeof resultado.estado === 'number') {
          setEstadoActual(resultado.estado);
        }
      } catch (error) {
        const messages = resolveGrabacionErrorMessages(error, t);
        if (messages.length > 0) {
          setErroresDialogContext('grabacion');
          setErroresGrabacionMessages(messages);
          setErroresGrabacionVisible(true);
          setSaveError(null);
        } else {
          setSaveError(t('pedidos.carga.errorGrabacion'));
        }
      } finally {
        setIsLoading(false);
      }
    },
    [
      bonificacionNetaCabecera,
      cabecera,
      codComprobanteOrigenCopia,
      codPedidoActual,
      codPedidoOrigen,
      codPresupuestoOrigen,
      comprobanteId,
      estadoActual,
      modo,
      readOnly,
      renglones,
      selectedCliente,
      t,
    ],
  );

  const canAdvanceFromStep = useCallback(
    (currentStep: PedidosCargaMobileStep): boolean => {
      if (currentStep === 'cliente') {
        return selectedCliente !== null && !articulosStockPrecargaPendiente;
      }

      if (currentStep === 'cabecera') {
        return cabecera !== null && !cabeceraLoading;
      }

      if (currentStep === 'articulos') {
        return renglonesVisibles.length > 0;
      }

      return true;
    },
    [
      articulosStockPrecargaPendiente,
      cabecera,
      cabeceraLoading,
      renglonesVisibles.length,
      selectedCliente,
    ],
  );

  const goToNextStep = useCallback(() => {
    const currentIndex = stepOrder.indexOf(step);
    if (currentIndex < 0 || currentIndex >= stepOrder.length - 1) {
      return;
    }

    if (!canAdvanceFromStep(step)) {
      return;
    }

    setStep(stepOrder[currentIndex + 1]!);
  }, [canAdvanceFromStep, step]);

  const goToPreviousStep = useCallback(() => {
    const currentIndex = stepOrder.indexOf(step);
    if (currentIndex <= 0) {
      return;
    }

    setStep(stepOrder[currentIndex - 1]!);
  }, [step]);

  const clienteLabel = useMemo(() => {
    if (!selectedCliente) {
      return '';
    }

    const cliente = clientesOrdenados.find((item) => item.codCliente === selectedCliente);
    return cliente ? etiquetaCliente(cliente) : selectedCliente;
  }, [clientesOrdenados, selectedCliente]);

  const articuloDisplayExpr = useCallback(
    (item: ArticuloOption | null) => (item ? etiquetaArticulo(item, t) : ''),
    [t],
  );

  return {
    step,
    setStep,
    stepOrder,
    goToNextStep,
    goToPreviousStep,
    canAdvanceFromStep,
    modo,
    readOnly,
    isLoading,
    cabeceraLoading,
    saveError,
    successMessage,
    confirmacionVisible,
    setConfirmacionVisible,
    erroresGrabacionVisible,
    setErroresGrabacionVisible,
    erroresGrabacionMessages,
    erroresDialogContext,
    handleErroresDialogClose,
    tipoComprobanteLabel,
    showGrabarPedido,
    showGrabarPresupuesto,
    clientesOrdenados,
    clientesLoading,
    selectedCliente,
    clienteLabel,
    isClienteProfile,
    handleClienteChange,
    cabecera,
    setCabecera,
    catalogos,
    parametrosCarga,
    handleCabeceraChange,
    modificaListaPrec,
    modificaPrecio,
    modificaBonArt,
    bonificacionNetaCabecera,
    totales,
    renglones,
    setRenglones,
    renglonesVisibles,
    articulosOrdenados,
    articuloSeleccionado,
    setArticuloSeleccionado,
    articuloSeleccionadoData,
    articuloDisplayExpr,
    articulosStockPrecargaPendiente,
    articulosPreciosLoading,
    handleAgregarArticulo,
    handleEliminarRenglon,
    handleGuardarRenglon,
    abrirEdicionRenglon,
    renglonEnEdicion,
    editDialogVisible,
    cerrarEdicionRenglon,
    handleCancelar,
    handlePostGrabacion,
    saveComprobante,
  };
}
