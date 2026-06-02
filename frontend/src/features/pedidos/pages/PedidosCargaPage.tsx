import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import Toast from 'devextreme-react/toast';
import { Column } from 'devextreme-react/data-grid';
import { DataGridDx } from '../../../shared/ui/grids';
import {
  fetchClientes,
  fetchComprobante,
  grabarComprobante,
  type ClienteOption,
  type ComprobanteRenglon,
} from '../api/comprobanteApi';

const proceso = 'pw_cargapedidos';
const gridId = 'grid-renglones-carga';

function createDefaultRenglon(renglon = 1): ComprobanteRenglon {
  return {
    renglon,
    codArticulo: 'ART-001',
    descripcionArticulo: 'Artículo demo',
    cantidad: 1,
    precio: 100,
    porcBonif: 0,
    porcIva: 21,
  };
}

export function PedidosCargaPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { t } = useTranslation();
  const [clientes, setClientes] = useState<ClienteOption[]>([]);
  const [selectedCliente, setSelectedCliente] = useState<string | null>(null);
  const [codPedidoActual, setCodPedidoActual] = useState<string | null>(null);
  const [estadoActual, setEstadoActual] = useState<number | null>(null);
  const [codPedidoOrigen, setCodPedidoOrigen] = useState<string | null>(null);
  const [codPresupuestoOrigen, setCodPresupuestoOrigen] = useState<string | null>(null);
  const [renglones, setRenglones] = useState<ComprobanteRenglon[]>([createDefaultRenglon()]);
  const [isLoading, setIsLoading] = useState(false);
  const [mailToastVisible, setMailToastVisible] = useState(false);
  const [pendingMailToast, setPendingMailToast] = useState(false);
  const [successToastVisible, setSuccessToastVisible] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [confirmacionVisible, setConfirmacionVisible] = useState(false);
  const [mailAvisoVisible, setMailAvisoVisible] = useState(false);

  const modo = searchParams.get('modo') ?? 'nuevo';
  const tipoComprobanteLabel =
    estadoActual === 99 || searchParams.get('tipoOrigen') === 'presupuesto'
      ? t('pedidos.carga.tipoPresupuesto')
      : t('pedidos.carga.tipoPedido');

  useEffect(() => {
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
  }, []);

  useEffect(() => {
    const comprobanteId = searchParams.get('codComprobante') ?? searchParams.get('id');
    if (!comprobanteId) {
      return;
    }

    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      try {
        const comprobante = await fetchComprobante(comprobanteId);
        if (!mounted) {
          return;
        }

        setCodPedidoActual(modo === 'copia' ? null : comprobante.codPedido);
        setEstadoActual(comprobante.estado);
        setSelectedCliente(comprobante.codCliente ?? null);
        setRenglones(comprobante.renglones.length > 0 ? comprobante.renglones : [createDefaultRenglon()]);

        if (comprobante.estado === 99) {
          setCodPresupuestoOrigen(comprobante.codPedido);
        } else if (comprobante.estado === 0 || comprobante.estado === -1) {
          setCodPedidoOrigen(comprobante.codPedido);
        }
      } catch {
        if (mounted) {
          setRenglones([createDefaultRenglon()]);
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
  }, [modo, searchParams]);

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

  const saveComprobante = async (accionGrabacion: 'pedido' | 'presupuesto') => {
    if (!selectedCliente) {
      return;
    }

    setIsLoading(true);
    try {
      const codPedidoParaGrabar =
        accionGrabacion === 'pedido' && estadoActual === 99 ? null : codPedidoActual;

      const codPresupuestoParaConversion =
        accionGrabacion === 'pedido' && estadoActual === 99
          ? (codPresupuestoOrigen ?? codPedidoActual)
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
          accionGrabacion === 'pedido' && estadoActual === 99 ? codPresupuestoParaConversion : null,
        codCliente: selectedCliente,
        renglones,
      });

      const resultado = response.resultado;
      const nroVisible = resultado.nro_visible ?? 0;
      const guidSufijo = resultado.guidSufijo ?? '';

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
      <div className="pedidosCargaPage__toolbar">
        <div data-testid="btn-grabar-pedido">
          <Button
            text={t('pedidos.carga.grabarPedido')}
            type="default"
            stylingMode="contained"
            onClick={() => {
              void saveComprobante('pedido');
            }}
          />
        </div>
        <div data-testid="btn-grabar-presupuesto">
          <Button
            text={t('pedidos.carga.grabarPresupuesto')}
            type="default"
            stylingMode="outlined"
            onClick={() => {
              void saveComprobante('presupuesto');
            }}
          />
        </div>
        <div data-testid="btn-cancelar-carga">
          <Button
            text={t('pedidos.carga.cancelar')}
            stylingMode="text"
            onClick={() => navigate(-1)}
          />
        </div>
      </div>

      <section data-testid="form-cabecera-carga">
        <h3>{t('pedidos.carga.cabeceraTitle')}</h3>
        {selectedCliente ? <span data-testid="cliente-cargado" hidden aria-hidden="true" /> : null}
        <SelectBox
          dataSource={clientes}
          valueExpr="codCliente"
          displayExpr="nombre"
          value={selectedCliente}
          onValueChanged={(event) => {
            setSelectedCliente((event.value as string | null) ?? null);
          }}
          placeholder={t('pedidos.carga.clientePlaceholder')}
          inputAttr={{ 'data-testid': 'cliente-select' }}
        />
      </section>

      <DataGridDx<ComprobanteRenglon>
        proceso={proceso}
        gridId={gridId}
        dataSource={renglones}
        keyExpr="renglon"
        isLoading={isLoading}
        exportEnabled={false}
      >
        <Column dataField="codArticulo" caption={t('pedidos.carga.grid.articulo')} />
        <Column dataField="cantidad" caption={t('pedidos.carga.grid.cantidad')} dataType="number" />
        <Column dataField="precio" caption={t('pedidos.carga.grid.precio')} dataType="number" format="currency" />
        <Column
          dataField="porcBonif"
          caption={t('pedidos.carga.grid.bonificacion')}
          dataType="number"
          format="#0.##'%'"
        />
      </DataGridDx>

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
