import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { custom } from 'devextreme/ui/dialog';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import { SelectBoxDx } from '../../../shared/ui/controls/SelectBoxDx';
import Toast from 'devextreme-react/toast';
import { isDevExtremeUserChange } from '../../../shared/ui/devextremeUserChange';
import { useRequiredSessionContext } from '../../auth/AuthProvider';
import { fetchPublicConfig } from '../../config/api/publicConfigApi';
import { ExcelImportHostToolbar } from '../../excelImport/components/ExcelImportHostToolbar';
import type { ExcelImportHostResult } from '../../excelImport/types/excelImportHostTypes';
import {
  cancelarEdicionPedido,
  fetchArticulosCatalogoCarga,
  fetchArticuloCargaByCodigo,
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
import { ComprobanteCabeceraForm } from '../components/ComprobanteCabeceraForm';
import { ComprobanteLeyendasPie } from '../components/ComprobanteLeyendasPie';
import TextArea from 'devextreme-react/text-area';
import { PedidosCargaConfirmacionDialog } from '../components/PedidosCargaConfirmacionDialog';
import { PedidosCargaErroresGrabacionDialog } from '../components/PedidosCargaErroresGrabacionDialog';
import { PedidosCargaRenglonesGrid } from '../components/PedidosCargaRenglonesGrid';
import {
  emptyComprobanteCabecera,
  type CabeceraCatalogos,
  type ComprobanteCabecera,
} from '../types/comprobanteCabecera';
import {
  type ClienteSortField,
  etiquetaCliente,
  etiquetaArticulo,
  ordenarArticulosPorDescripcion,
  ordenarClientes,
} from '../utils/cargaCatalogos';
import { actualizarPreciosRenglonesPorLista } from '../utils/actualizarPreciosRenglones';
import { EXCEL_PROCESO_PEDIDO_INDIVIDUAL } from '../constants/excelImportCarga';
import {
  isPedidosCargaExcelImportDisabled,
  mapExcelRowToCabecera,
  mapExcelRowsToRenglones,
} from '../utils/mapExcelImportToCarga';
import {
  calcularBonificacionNeta,
  calcularTotalesComprobante,
  createEmptyRenglon,
  formatImporteMoneda,
  nextRenglonNumber,
  normalizarPorcIvaAlmacenado,
  renglonesValidosParaGrabar,
  tieneRenglonesCargados,
} from '../utils/renglonesCarga';
import { resolveGrabacionErrorMessages } from '../utils/resolveGrabacionErrorMessages';
import './PedidosCargaPage.css';

const emptyCatalogos: CabeceraCatalogos = {
  condicionesVenta: [],
  transportes: [],
  listasPrecios: [],
  direccionesEntrega: [],
  perfiles: [],
};

export function PedidosCargaPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const sessionContext = useRequiredSessionContext();
  const { t } = useTranslation();
  const edicionIniciadaRef = useRef(false);
  const codPedidoEdicionRef = useRef<string | null>(null);
  const ultimaAccionGrabacionRef = useRef<'pedido' | 'presupuesto' | null>(null);
  const isHydratingComprobanteRef = useRef(false);
  const hydratingFromExcelImportRef = useRef(false);

  const [clientes, setClientes] = useState<ClienteOption[]>([]);
  const [clientesLoading, setClientesLoading] = useState(false);
  const [selectedCliente, setSelectedCliente] = useState<string | null>(null);
  const [cabecera, setCabecera] = useState<ComprobanteCabecera | null>(null);
  const [catalogos, setCatalogos] = useState<CabeceraCatalogos>(emptyCatalogos);
  const [parametrosCarga, setParametrosCarga] = useState<ParametrosCarga | null>(null);
  const [articuloSeleccionado, setArticuloSeleccionado] = useState<string | null>(null);
  const [articuloSeleccionadoData, setArticuloSeleccionadoData] = useState<ArticuloOption | null>(null);
  const [articulos, setArticulos] = useState<ArticuloOption[]>([]);
  const [articulosLoading, setArticulosLoading] = useState(false);
  const [codPedidoActual, setCodPedidoActual] = useState<string | null>(null);
  const [estadoActual, setEstadoActual] = useState<number | null>(null);
  const [codPedidoOrigen, setCodPedidoOrigen] = useState<string | null>(null);
  const [codPresupuestoOrigen, setCodPresupuestoOrigen] = useState<string | null>(null);
  const [codComprobanteOrigenCopia, setCodComprobanteOrigenCopia] = useState<string | null>(null);
  const [renglones, setRenglones] = useState<ComprobanteRenglon[]>([createEmptyRenglon(1)]);
  const renglonesRef = useRef(renglones);
  renglonesRef.current = renglones;
  const listaPreciosAnteriorRef = useRef<number | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [cabeceraLoading, setCabeceraLoading] = useState(false);
  const [mailToastVisible, setMailToastVisible] = useState(false);
  const [pendingMailToast, setPendingMailToast] = useState(false);
  const [successToastVisible, setSuccessToastVisible] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [confirmacionVisible, setConfirmacionVisible] = useState(false);
  const [mailAvisoVisible, setMailAvisoVisible] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [erroresGrabacionVisible, setErroresGrabacionVisible] = useState(false);
  const [erroresGrabacionMessages, setErroresGrabacionMessages] = useState<string[]>([]);
  const [clienteSelectKey, setClienteSelectKey] = useState(0);
  const [clienteSortField, setClienteSortField] = useState<ClienteSortField>('razonSocial');
  const [autoOpenRenglonId, setAutoOpenRenglonId] = useState<number | null>(null);
  const [excelImportEnabled, setExcelImportEnabled] = useState(false);

  const modo = searchParams.get('modo') ?? 'nuevo';
  const comprobanteId = searchParams.get('codComprobante') ?? searchParams.get('id');
  const readOnly = modo === 'ver';
  const isClienteProfile =
    sessionContext.functionalProfile === 'cliente' || sessionContext.codCliente !== null;

  const excelImportDisabled = useMemo(
    () =>
      isPedidosCargaExcelImportDisabled({
        excelImportEnabled,
        readOnly,
        modo,
        comprobanteId: comprobanteId ?? null,
        renglones,
        isClienteProfile,
        selectedCliente,
      }),
    [
      comprobanteId,
      excelImportEnabled,
      isClienteProfile,
      modo,
      readOnly,
      renglones,
      selectedCliente,
    ],
  );

  const clientesOrdenados = useMemo(
    () => ordenarClientes(clientes, clienteSortField),
    [clientes, clienteSortField],
  );

  const articulosOrdenados = useMemo(
    () => ordenarArticulosPorDescripcion(articulos),
    [articulos],
  );

  const clienteSortOptions = useMemo(
    () =>
      (['codCliente', 'razonSocial', 'nombreFantasia'] as const).map((field) => ({
        value: field,
        label: t(`pedidos.carga.clienteOrden.${field}`),
      })),
    [t],
  );

  const clienteNombre = useMemo(() => {
    if (!selectedCliente) {
      return '';
    }

    const cliente = clientes.find((item) => item.codCliente === selectedCliente);
    return cliente ? etiquetaCliente(cliente) : selectedCliente;
  }, [clientes, selectedCliente]);

  const handleCabeceraChange = useCallback((next: ComprobanteCabecera) => {
    setCabecera({
      ...next,
      descuento: calcularBonificacionNeta(next.bonif1, next.bonif2, next.bonif3),
    });
  }, []);

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
    if (hydratingFromExcelImportRef.current) {
      return;
    }

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

  const loadArticulosCatalogo = useCallback(async () => {
    setArticulosLoading(true);

    try {
      const data = await fetchArticulosCatalogoCarga();
      setArticulos(data);
    } catch {
      setArticulos([]);
    } finally {
      setArticulosLoading(false);
    }
  }, []);

  useEffect(() => {
    let mounted = true;

    void fetchPublicConfig()
      .then((config) => {
        if (mounted) {
          setExcelImportEnabled(config.excelImportEnabled);
        }
      })
      .catch(() => {
        if (mounted) {
          setExcelImportEnabled(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

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
        const data = await fetchClientes();
        if (!mounted) {
          return;
        }

        setClientes(data);
      } catch {
        if (!mounted) {
          return;
        }

        setClientes([]);
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
  }, [comprobanteId, isClienteProfile, loadCabeceraForCliente, modo, sessionContext.codCliente]);

  useEffect(() => {
    void loadArticulosCatalogo();
  }, [loadArticulosCatalogo]);

  useEffect(() => {
    if (!selectedCliente || comprobanteId || modo !== 'nuevo') {
      return;
    }

    if (hydratingFromExcelImportRef.current) {
      return;
    }

    void loadCabeceraForCliente(selectedCliente);
  }, [comprobanteId, loadCabeceraForCliente, modo, selectedCliente]);

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
          setSelectedCliente(null);
          setCabecera(null);
          setCatalogos(emptyCatalogos);
          setRenglones([createEmptyRenglon(1)]);
          setSaveError(t('pedidos.carga.errorCargaComprobante'));
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
          window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => {
              isHydratingComprobanteRef.current = false;
            });
          });
        } else {
          isHydratingComprobanteRef.current = false;
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [comprobanteId, modo, sessionContext.codCliente, t]);

  useEffect(
    () => () => {
      const codPedido = codPedidoEdicionRef.current;
      if (edicionIniciadaRef.current && codPedido) {
        void cancelarEdicionPedido(codPedido).catch(() => undefined);
      }
    },
    [],
  );

  const confirmarCambioCliente = useCallback(
    () =>
      new Promise<boolean>((resolve) => {
        if (!tieneRenglonesCargados(renglones)) {
          resolve(true);
          return;
        }

        const dialog = custom({
          title: t('pedidos.carga.cambioClienteTitulo'),
          messageHtml: t('pedidos.carga.cambioClienteMensaje'),
          dragEnabled: false,
          buttons: [
            {
              text: t('pedidos.carga.cambioClienteCancelar'),
              onClick: () => resolve(false),
            },
            {
              text: t('pedidos.carga.cambioClienteConfirmar'),
              type: 'default',
              onClick: () => resolve(true),
            },
          ],
        });

        dialog.show();
      }),
    [renglones, t],
  );

  const handleClienteChange = useCallback(
    async (codCliente: string | null) => {
      if (isHydratingComprobanteRef.current || hydratingFromExcelImportRef.current) {
        return;
      }

      if (!codCliente) {
        setSelectedCliente(null);
        setCabecera(null);
        setCatalogos(emptyCatalogos);
        setArticuloSeleccionado(null);
        setArticuloSeleccionadoData(null);
        setRenglones([createEmptyRenglon(1)]);
        return;
      }

      setSelectedCliente(codCliente);
      setRenglones([createEmptyRenglon(1)]);
      setArticuloSeleccionado(null);
      setArticuloSeleccionadoData(null);
      await loadCabeceraForCliente(codCliente);
    },
    [loadCabeceraForCliente],
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
    setArticuloSeleccionadoData(null);
    setAutoOpenRenglonId(null);
    setSaveError(null);
    setErroresGrabacionVisible(false);
    setErroresGrabacionMessages([]);
    setConfirmacionVisible(false);
    setSuccessToastVisible(false);
    setSuccessMessage('');
    setMailAvisoVisible(false);
    setMailToastVisible(false);
    setPendingMailToast(false);

    if (isClienteProfile) {
      const codCliente = sessionContext.codCliente ?? selectedCliente ?? '';
      if (codCliente) {
        setSelectedCliente(codCliente);
        await loadCabeceraForCliente(codCliente);
      }
      return;
    }

    setSelectedCliente(null);
    setCabecera(null);
    setCatalogos(emptyCatalogos);
    setClienteSelectKey((value) => value + 1);

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
        // Prioriza limpiar pantalla; el backend puede liberar bloqueo por timeout.
      }
    }

    await resetParaNuevoComprobante();
  }, [resetParaNuevoComprobante]);

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

  const handleAgregarArticulo = useCallback(async () => {
    if (readOnly || !articuloSeleccionado || !articuloSeleccionadoData || !cabecera) {
      return;
    }

    let articulo = articuloSeleccionadoData;
    const codLista = Number(cabecera.listaPrecios);
    if (!Number.isNaN(codLista) && codLista > 0) {
      try {
        const fetched = await fetchArticuloCargaByCodigo(articulo.codArticulo, codLista);
        if (fetched) {
          articulo = fetched;
        }
      } catch {
        // Mantiene datos del ítem seleccionado si falla la consulta puntual.
      }
    }

    const renglonesActivos = renglonesValidosParaGrabar(renglones);
    const yaExiste = renglonesActivos.some((renglon) => renglon.codArticulo === articulo.codArticulo);
    if (yaExiste) {
      setSaveError(t('pedidos.carga.articuloDuplicado'));
      return;
    }

    setSaveError(null);
    const sinVacios = renglonesValidosParaGrabar(renglones);
    const nuevoRenglon: ComprobanteRenglon = {
      renglon: nextRenglonNumber(sinVacios),
      codArticulo: articulo.codArticulo,
      descripcionArticulo: articulo.descripcion,
      cantidad: 1,
      precio: articulo.precio ?? 0,
      porcBonif: articulo.bonificacion,
      porcIva: normalizarPorcIvaAlmacenado(articulo.porcIva),
    };

    setRenglones([...sinVacios, nuevoRenglon]);
    setAutoOpenRenglonId(nuevoRenglon.renglon);
    setArticuloSeleccionado(null);
    setArticuloSeleccionadoData(null);
  }, [articuloSeleccionado, articuloSeleccionadoData, cabecera, readOnly, renglones, t]);

  const listaPreciosArticulosValida = useMemo(() => {
    const codLista = Number(cabecera?.listaPrecios);
    return (
      cabecera?.listaPrecios !== null &&
      cabecera?.listaPrecios !== undefined &&
      !Number.isNaN(codLista) &&
      codLista > 0
    );
  }, [cabecera?.listaPrecios]);

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
  const modificaPrecio = parametrosCarga?.modificaPrecio ?? true;
  const modificaBonArt = parametrosCarga?.modificaBonArt ?? true;
  const monedaSimbolo = '$';

  const saveComprobante = async (accionGrabacion: 'pedido' | 'presupuesto') => {
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
      const nroVisible = resultado.nro_visible ?? 0;
      const guidSufijo = resultado.guidSufijo ?? '';

      edicionIniciadaRef.current = false;
      codPedidoEdicionRef.current = null;
      ultimaAccionGrabacionRef.current = accionGrabacion;

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

      if (typeof resultado.estado === 'number') {
        setEstadoActual(resultado.estado);
      }

      if (resultado.mailEnviado === false) {
        setPendingMailToast(true);
        setMailAvisoVisible(true);
        window.setTimeout(() => {
          setMailToastVisible(true);
        }, 500);
      }
    } catch (error) {
      const messages = resolveGrabacionErrorMessages(error, t);
      if (messages.length > 0) {
        setErroresGrabacionMessages(messages);
        setErroresGrabacionVisible(true);
        setSaveError(null);
      } else {
        setSaveError(t('pedidos.carga.errorGrabacion'));
      }
    } finally {
      setIsLoading(false);
    }
  };

  const cabeceraReady = cabecera !== null && selectedCliente !== null;

  const handleExcelImportComplete = useCallback(
    async (result: ExcelImportHostResult) => {
      if (result.validRows.length === 0) {
        return;
      }

      const firstRow = result.validRows[0];
      const codCliente = String(firstRow.cod_cliente ?? '').trim();
      if (!codCliente) {
        return;
      }

      hydratingFromExcelImportRef.current = true;
      setSaveError(null);

      try {
        const cabeceraResult = await fetchCabeceraInicial(codCliente);
        const mergedCabecera = mapExcelRowToCabecera(firstRow, cabeceraResult.cabecera);
        mergedCabecera.descuento = calcularBonificacionNeta(
          mergedCabecera.bonif1,
          mergedCabecera.bonif2,
          mergedCabecera.bonif3,
        );
        const importedRenglones = mapExcelRowsToRenglones(result.validRows);

        setSelectedCliente(codCliente);
        setCatalogos(cabeceraResult.catalogos);
        setCabecera(mergedCabecera);
        setRenglones(importedRenglones);
        setArticuloSeleccionado(null);
        setArticuloSeleccionadoData(null);
        setAutoOpenRenglonId(null);
        setSuccessMessage(t('pedidos.carga.excelImport.importSuccess'));
        setSuccessToastVisible(true);
      } catch {
        setSaveError(t('pedidos.carga.errorCargaComprobante'));
      } finally {
        window.requestAnimationFrame(() => {
          window.requestAnimationFrame(() => {
            hydratingFromExcelImportRef.current = false;
          });
        });
      }
    },
    [t],
  );

  return (
    <section className="pedidosCargaPage" data-testid="page-pedidos-carga">
      <div className="pedidosCargaPage__header">
        <h2>{t('pages.pedidosCarga')}</h2>
        <p className="pedidosCargaPage__tipo" data-testid="label-tipo-comprobante">
          {tipoComprobanteLabel}
        </p>
      </div>

      {readOnly ? (
        <p data-testid="label-modo-solo-lectura">{t('pedidos.carga.modoSoloLectura')}</p>
      ) : null}

      {excelImportEnabled ? (
        <div className="pedidosCargaExcelToolbar" data-testid="pedidos-carga-excel-toolbar">
          <p className="pedidosCargaExcelToolbar__hint">{t('pedidos.carga.excelImport.toolbarHint')}</p>
          <ExcelImportHostToolbar
            codigoProceso={EXCEL_PROCESO_PEDIDO_INDIVIDUAL}
            disabled={excelImportDisabled}
            onComplete={(result) => {
              void handleExcelImportComplete(result);
            }}
          />
        </div>
      ) : null}

      <div className="pedidosCargaPage__toolbar">
        <div className="pedidosCargaPage__toolbarLeft">
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
        <div className="pedidosCargaPage__toolbarCenter">
          {showGrabarPresupuesto ? (
            <div data-testid="btn-grabar-presupuesto">
              <Button
                text={t('pedidos.carga.grabarPresupuesto')}
                type="default"
                stylingMode="outlined"
                disabled={isLoading || !cabeceraReady}
                onClick={() => {
                  void saveComprobante('presupuesto');
                }}
              />
            </div>
          ) : null}
        </div>
        <div className="pedidosCargaPage__toolbarRight">
          {showGrabarPedido ? (
            <div data-testid="btn-grabar-pedido">
              <Button
                text={t('pedidos.carga.grabarPedido')}
                type="default"
                stylingMode="contained"
                disabled={isLoading || !cabeceraReady}
                onClick={() => {
                  void saveComprobante('pedido');
                }}
              />
            </div>
          ) : null}
        </div>
      </div>

      {saveError ? (
        <p className="pedidosCargaPage__error" role="alert" data-testid="carga-error">
          {saveError}
        </p>
      ) : null}

      <div className="pedidosCargaPage__body">
        <section className="pedidosCargaPage__panel pedidosCargaPage__cabeceraBand" data-testid="form-cabecera-carga">
          <h3 className="pedidosCargaPage__panelTitle">{t('pedidos.carga.cabeceraTitle')}</h3>
          {selectedCliente ? <span data-testid="cliente-cargado" hidden aria-hidden="true" /> : null}

          {isClienteProfile ? (
            <p data-testid="cliente-fijo">{clienteNombre || selectedCliente}</p>
          ) : (
            <div className="pedidosCargaPage__clienteRow">
              <SelectBox
                label={t('pedidos.carga.clienteOrdenarPor')}
                dataSource={clienteSortOptions}
                valueExpr="value"
                displayExpr="label"
                value={clienteSortField}
                onValueChanged={(event) => {
                  if (!isDevExtremeUserChange(event)) {
                    return;
                  }

                  setClienteSortField((event.value as ClienteSortField) ?? 'razonSocial');
                }}
                inputAttr={{ 'data-testid': 'cliente-orden-select' }}
              />
              <SelectBoxDx
                key={`cliente-select-${clienteSelectKey}`}
                dataSource={clientesOrdenados}
                valueExpr="codCliente"
                displayExpr={(item: ClienteOption | null) =>
                  item ? etiquetaCliente(item) : ''
                }
                searchEnabled={true}
                searchExpr={['codCliente', 'razonSocial', 'nombreFantasia', 'nombre']}
                value={selectedCliente}
                readOnly={readOnly}
                isLoading={clientesLoading}
                autoSelectSingleMatch={!readOnly}
                onValueChanged={(event) => {
                  if (isHydratingComprobanteRef.current || !isDevExtremeUserChange(event)) {
                    return;
                  }

                  const nextCliente = (event.value as string | null) ?? null;
                  if (nextCliente === selectedCliente) {
                    return;
                  }

                  void (async () => {
                    const confirmed = await confirmarCambioCliente();
                    if (!confirmed) {
                      setClienteSelectKey((value) => value + 1);
                      return;
                    }

                    await handleClienteChange(nextCliente);
                  })();
                }}
                placeholder={t('pedidos.carga.clientePlaceholder')}
                showClearButton={!readOnly}
                inputAttr={{ 'data-testid': 'cliente-select' }}
              />
            </div>
          )}

          {!selectedCliente && !isClienteProfile ? (
            <p>{t('pedidos.carga.seleccioneCliente')}</p>
          ) : null}
          {selectedCliente && cabeceraReady && cabecera && !cabeceraLoading ? (
            <ComprobanteCabeceraForm
              cabecera={cabecera}
              catalogos={catalogos}
              parametrosCarga={parametrosCarga}
              readOnly={readOnly}
              clienteNombre={isClienteProfile ? clienteNombre : undefined}
              onChange={handleCabeceraChange}
            />
          ) : null}
          {selectedCliente && !cabeceraReady && cabeceraLoading ? (
            <p>{t('pedidos.carga.cabeceraCargando')}</p>
          ) : null}
        </section>

        <div className="pedidosCargaPage__middle">
          <aside className="pedidosCargaPage__leyendasColumn">
            {cabeceraReady && cabecera ? (
              <ComprobanteLeyendasPie
                cabecera={cabecera}
                readOnly={readOnly}
                onChange={setCabecera}
              />
            ) : null}
          </aside>

          <div className="pedidosCargaPage__gridColumn">
            {!readOnly ? (
              <section className="pedidosCargaPage__panel" data-testid="form-articulo-carga">
                <h3 className="pedidosCargaPage__panelTitle">{t('pedidos.carga.articulosTitle')}</h3>
                {articulosLoading && articulos.length === 0 ? (
                  <p data-testid="articulos-cargando">{t('pedidos.carga.articulosCargando')}</p>
                ) : null}
                <div className="pedidosCargaPage__articuloRow">
                  <SelectBoxDx
                    dataSource={articulosOrdenados}
                    valueExpr="codArticulo"
                    displayExpr={(item: ArticuloOption | null) =>
                      item ? etiquetaArticulo(item, t) : ''
                    }
                    value={articuloSeleccionado}
                    searchEnabled={true}
                    searchExpr={['codArticulo', 'descripcion']}
                    searchMode="contains"
                    isLoading={articulosLoading}
                    autoSelectSingleMatch={true}
                    disabled={!listaPreciosArticulosValida || articulosLoading}
                    onValueChanged={(event) => {
                      setArticuloSeleccionado((event.value as string | null) ?? null);
                      setArticuloSeleccionadoData(
                        (event.component.option('selectedItem') as ArticuloOption | null) ?? null,
                      );
                    }}
                    placeholder={t('pedidos.carga.articuloPlaceholder')}
                    inputAttr={{ 'data-testid': 'articulo-select' }}
                  />
                  <div data-testid="articulosRefresh">
                    <Button
                      icon="refresh"
                      stylingMode="outlined"
                      hint={t('grid.refresh')}
                      disabled={articulosLoading}
                      onClick={() => {
                        void loadArticulosCatalogo();
                      }}
                    />
                  </div>
                  <div data-testid="btn-agregar-articulo">
                    <Button
                      text={t('pedidos.carga.agregarArticulo')}
                      stylingMode="outlined"
                      disabled={!articuloSeleccionado}
                      onClick={() => {
                        void handleAgregarArticulo();
                      }}
                    />
                  </div>
                </div>
              </section>
            ) : null}

            <section className="pedidosCargaPage__panel">
              <h3 className="pedidosCargaPage__panelTitle">{t('pedidos.carga.renglonesTitle')}</h3>
              <PedidosCargaRenglonesGrid
                renglones={renglones}
                readOnly={readOnly}
                isLoading={isLoading}
                modificaPrecio={modificaPrecio}
                modificaBonArt={modificaBonArt}
                bonificacionNetaCabecera={bonificacionNetaCabecera}
                monedaSimbolo={monedaSimbolo}
                autoOpenRenglonId={autoOpenRenglonId}
                onAutoOpenConsumed={() => setAutoOpenRenglonId(null)}
                onRenglonesChange={setRenglones}
              />
            </section>
          </div>
        </div>

        <div className="pedidosCargaPage__footer">
          {cabeceraReady && cabecera ? (
            <section className="pedidosCargaPage__panel pedidosCargaPage__observacionesPanel">
              <div className="pedidosCargaPage__observacionesField">
                <TextArea
                  label={t('pedidos.carga.cabecera.observaciones')}
                  value={cabecera.observaciones}
                  height="100%"
                  readOnly={readOnly}
                  onValueChanged={(event) => {
                    if (!isDevExtremeUserChange(event)) {
                      return;
                    }

                    setCabecera({
                      ...cabecera,
                      observaciones: String(event.value ?? ''),
                    });
                  }}
                  inputAttr={{ 'data-testid': 'cabecera-observaciones' }}
                />
              </div>
            </section>
          ) : (
            <div className="pedidosCargaPage__observacionesPlaceholder" />
          )}

          <section className="pedidosCargaPage__panel pedidosCargaPage__totalesPanel" data-testid="totales-carga">
            <h3 className="pedidosCargaPage__panelTitle">{t('pedidos.carga.totalesTitle')}</h3>
            {mailAvisoVisible ? (
              <p data-testid="aviso-mail-envio-fallido" role="status">
                {t('pedidos.carga.mailEnvioFallido')}
              </p>
            ) : null}
            <div className="pedidosCargaPage__totales">
              <p>
                <span>{t('pedidos.carga.subtotal')}</span>
                <span data-testid="totales-subtotal">
                  {formatImporteMoneda(monedaSimbolo, totales.subtotal)}
                </span>
              </p>
              <p>
                <span>{t('pedidos.carga.iva')}</span>
                <span data-testid="totales-iva">
                  {formatImporteMoneda(monedaSimbolo, totales.iva)}
                </span>
              </p>
              <p className="pedidosCargaPage__totalesTotal">
                <span>{t('pedidos.carga.total')}</span>
                <span data-testid="totales-total">
                  {formatImporteMoneda(monedaSimbolo, totales.total)}
                </span>
              </p>
            </div>
          </section>
        </div>
      </div>

      <PedidosCargaConfirmacionDialog
        visible={confirmacionVisible}
        message={successMessage}
        onClose={() => {
          setConfirmacionVisible(false);
          handlePostGrabacion();
        }}
      />

      <PedidosCargaErroresGrabacionDialog
        visible={erroresGrabacionVisible}
        messages={erroresGrabacionMessages}
        onClose={() => {
          setErroresGrabacionVisible(false);
        }}
      />

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
