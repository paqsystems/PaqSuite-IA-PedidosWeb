import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { CellPreparedEvent } from 'devextreme/ui/data_grid';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import { Column } from 'devextreme-react/data-grid';
import { fetchDeudaPorCliente, type DeudaConsultaRow } from '../../consultas/api/consultaApi';
import {
  buildDeudaSaldoResumen,
  deudaSaldoToneClassName,
  resolveDeudaSaldoCellTone,
} from '../../consultas/utils/deudaPresentacion';
import { DataGridDx } from '../../../shared/ui/grids';
import '../pages/PedidosCargaPage.css';
import '../../consultas/consultasShared.css';

type CargaDeudaSaldoPanelProps = {
  codCliente: string | null;
};

function formatSaldo(value: number, locale: string): string {
  return value.toLocaleString(locale, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

export function CargaDeudaSaldoPanel({ codCliente }: CargaDeudaSaldoPanelProps) {
  const { t, i18n } = useTranslation();
  const [filas, setFilas] = useState<DeudaConsultaRow[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [detalleVisible, setDetalleVisible] = useState(false);

  const loadDeuda = useCallback(async () => {
    if (!codCliente) {
      setFilas([]);
      setLoadError(null);
      return;
    }

    setIsLoading(true);
    setLoadError(null);

    try {
      const result = await fetchDeudaPorCliente(codCliente);
      setFilas(result.items);
    } catch {
      // Sin comprobantes → items []. Un error HTTP/SQL no debe confundirse con “sin deuda”.
      setFilas([]);
      setLoadError(t('pedidos.carga.deudaError'));
    } finally {
      setIsLoading(false);
    }
  }, [codCliente, t]);

  useEffect(() => {
    void loadDeuda();
  }, [loadDeuda]);

  const resumen = useMemo(() => buildDeudaSaldoResumen(filas), [filas]);

  const handleSaldoCellPrepared = useCallback((event: CellPreparedEvent) => {
    if (event.rowType !== 'data' || event.column?.dataField !== 'saldo' || !event.cellElement) {
      return;
    }

    const row = event.data as DeudaConsultaRow | undefined;
    if (!row) {
      return;
    }

    event.cellElement.classList.add(deudaSaldoToneClassName(resolveDeudaSaldoCellTone(row)));
  }, []);

  if (!codCliente) {
    return null;
  }

  const mostrarDetalle = resumen.saldoNeto !== 0;

  return (
    <div className="cargaDeudaSaldoPanel" data-testid="carga-deuda-panel">
      <span className="cargaDeudaSaldoPanel__label">{t('pedidos.carga.deudaSaldoLabel')}</span>
      {isLoading ? (
        <span className="cargaDeudaSaldoPanel__loading">{t('pedidos.carga.deudaCargando')}</span>
      ) : loadError ? (
        <span className="cargaDeudaSaldoPanel__error" role="alert">
          {loadError}
        </span>
      ) : (
        <span
          className={`cargaDeudaSaldoPanel__saldo ${deudaSaldoToneClassName(resumen.tone)}`}
          data-testid="cargaDeudaSaldo"
        >
          {formatSaldo(resumen.saldoNeto, i18n.language)}
        </span>
      )}
      {mostrarDetalle && !isLoading && !loadError ? (
        <Button
          icon="info"
          stylingMode="text"
          hint={t('pedidos.carga.deudaDetalleHint')}
          onClick={() => setDetalleVisible(true)}
          elementAttr={{ 'data-testid': 'cargaDeudaDetalleOpen' }}
        />
      ) : null}

      <Popup
        visible={detalleVisible}
        onHiding={() => setDetalleVisible(false)}
        dragEnabled={false}
        showCloseButton={true}
        width="90%"
        maxWidth={960}
        height={480}
        title={t('pedidos.carga.deudaDetalleTitulo')}
        elementAttr={{ 'data-testid': 'carga-deuda-detalle-popup' }}
      >
        <div className="cargaDeudaSaldoPanel__modal">
          <DataGridDx<DeudaConsultaRow>
            proceso="pw_carga_deuda"
            gridId="pw_carga_deuda_detalle"
            dataSource={filas}
            keyExpr="id"
            exportEnabled={false}
            enableGrouping={false}
            enableSummary={true}
            onCellPrepared={handleSaldoCellPrepared}
          >
            <Column dataField="tipo" caption={t('consultas.column.tipo')} />
            <Column dataField="numero" caption={t('consultas.column.numero')} />
            <Column
              dataField="fecha"
              caption={t('consultas.column.fecha')}
              dataType="date"
              format="dd/MM/yyyy"
            />
            <Column
              dataField="vencimiento"
              caption={t('consultas.column.vencimiento')}
              dataType="date"
              format="dd/MM/yyyy"
            />
            <Column
              dataField="saldo"
              caption={t('consultas.column.saldo')}
              dataType="number"
              format="#,##0.00"
            />
          </DataGridDx>
          <p
            className={`cargaDeudaSaldoPanel__total ${deudaSaldoToneClassName(resumen.tone)}`}
            data-testid="cargaDeudaDetalleTotal"
          >
            {t('pedidos.carga.deudaDetalleTotal', {
              total: formatSaldo(resumen.saldoNeto, i18n.language),
            })}
          </p>
        </div>
      </Popup>
    </div>
  );
}
