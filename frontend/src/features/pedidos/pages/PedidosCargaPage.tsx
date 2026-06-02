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

const defaultRenglones: ComprobanteRenglon[] = [
  {
    id: 'R1',
    articulo: 'ART-001',
    cantidad: 1,
    precio: 100,
    subtotal: 100,
  },
];

export function PedidosCargaPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { t } = useTranslation();
  const [clientes, setClientes] = useState<ClienteOption[]>([]);
  const [selectedCliente, setSelectedCliente] = useState<string | null>(null);
  const [renglones, setRenglones] = useState<ComprobanteRenglon[]>(defaultRenglones);
  const [isLoading, setIsLoading] = useState(false);
  const [mailToastVisible, setMailToastVisible] = useState(false);

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      try {
        const data = await fetchClientes();
        if (!mounted) {
          return;
        }

        setClientes(data);
        setSelectedCliente((previousValue) => previousValue ?? data[0]?.codigo ?? null);
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
    const comprobanteId = searchParams.get('id') ?? searchParams.get('codComprobante');
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

        setSelectedCliente(comprobante.codCliente ?? null);
        setRenglones(comprobante.renglones.length > 0 ? comprobante.renglones : defaultRenglones);
      } catch {
        if (mounted) {
          setRenglones(defaultRenglones);
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
  }, [searchParams]);

  const subtotal = useMemo(
    () => renglones.reduce((total, renglon) => total + Number(renglon.subtotal ?? 0), 0),
    [renglones],
  );
  const iva = useMemo(() => subtotal * 0.21, [subtotal]);
  const total = subtotal + iva;

  const saveComprobante = async (accionGrabacion: 'grabar_pedido' | 'grabar_presupuesto') => {
    setIsLoading(true);
    try {
      const response = await grabarComprobante({
        accionGrabacion,
        codCliente: selectedCliente,
        renglones,
      });

      if (response.resultado.mailEnviado === false) {
        setMailToastVisible(true);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <section data-testid="page-pedidos-carga">
      <h2>{t('pages.pedidosCarga')}</h2>
      <div className="pedidosCargaPage__toolbar">
        <Button
          text={t('pedidos.carga.grabarPedido')}
          type="default"
          stylingMode="contained"
          onClick={() => {
            void saveComprobante('grabar_pedido');
          }}
          elementAttr={{ 'data-testid': 'btn-grabar-pedido' }}
        />
        <Button
          text={t('pedidos.carga.grabarPresupuesto')}
          type="default"
          stylingMode="outlined"
          onClick={() => {
            void saveComprobante('grabar_presupuesto');
          }}
          elementAttr={{ 'data-testid': 'btn-grabar-presupuesto' }}
        />
        <Button
          text={t('pedidos.carga.cancelar')}
          stylingMode="text"
          onClick={() => navigate(-1)}
          elementAttr={{ 'data-testid': 'btn-cancelar-carga' }}
        />
      </div>

      <section data-testid="form-cabecera-carga">
        <h3>{t('pedidos.carga.cabeceraTitle')}</h3>
        <SelectBox
          dataSource={clientes}
          valueExpr="codigo"
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
        keyExpr="id"
        isLoading={isLoading}
        exportEnabled={false}
      >
        <Column dataField="articulo" caption={t('pedidos.carga.grid.articulo')} />
        <Column dataField="cantidad" caption={t('pedidos.carga.grid.cantidad')} dataType="number" />
        <Column dataField="precio" caption={t('pedidos.carga.grid.precio')} dataType="number" format="currency" />
        <Column
          dataField="subtotal"
          caption={t('pedidos.carga.grid.subtotal')}
          dataType="number"
          format="currency"
        />
      </DataGridDx>

      <section data-testid="totales-carga">
        <h3>{t('pedidos.carga.totalesTitle')}</h3>
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
