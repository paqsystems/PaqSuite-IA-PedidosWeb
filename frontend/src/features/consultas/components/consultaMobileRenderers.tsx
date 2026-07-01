import type { TFunction } from 'i18next';
import type { ConsultaDetailField } from '../../../shared/consultas/ConsultaDetailPopup';
import {
  formatConsultaAmount,
  formatConsultaDate,
} from '../../../shared/consultas/consultaMobileUtils';
import type {
  ChequeConsultaRow,
  ComprobanteConsultaRow,
  DeudaConsultaRow,
  DetallePedidoConsultaRow,
  HistorialVentasRow,
} from '../api/consultaApi';

export function renderComprobanteCard(item: ComprobanteConsultaRow, t: TFunction) {
  return (
    <article className="consultaKardexCard">
      <div className="consultaKardexCard__title">
        {item.numero || item.codPedido}
      </div>
      <div className="consultaKardexCard__subtitle">{item.razonSocial}</div>
      <div className="consultaKardexCard__metrics">
        <span>
          {t('consultas.column.fecha')}: {formatConsultaDate(item.fecha)}
        </span>
        <span>
          {t('consultas.column.importe')}: {formatConsultaAmount(item.importe)}
        </span>
      </div>
    </article>
  );
}

export function getComprobanteDetailFields(): ConsultaDetailField<ComprobanteConsultaRow>[] {
  return [
    { labelKey: 'consultas.column.numero', getValue: (item) => item.numero || item.codPedido },
    { labelKey: 'consultas.column.cliente', getValue: (item) => item.codCliente },
    { labelKey: 'consultas.column.razonSocial', getValue: (item) => item.razonSocial },
    { labelKey: 'consultas.column.fecha', getValue: (item) => formatConsultaDate(item.fecha) },
    { labelKey: 'consultas.column.importe', getValue: (item) => formatConsultaAmount(item.importe) },
    { labelKey: 'consultas.column.estado', getValue: (item) => String(item.estado) },
    {
      labelKey: 'consultas.column.observaciones',
      getValue: (item) => item.observaciones || '—',
      visible: (item) => item.observaciones.trim().length > 0,
    },
  ];
}

export function renderDeudaCard(item: DeudaConsultaRow, t: TFunction) {
  return (
    <article className="consultaKardexCard">
      <div className="consultaKardexCard__title">{item.razonSocial}</div>
      <div className="consultaKardexCard__subtitle">
        {item.tipo} {item.numero}
      </div>
      <div className="consultaKardexCard__metrics">
        <span>
          {t('consultas.column.saldo')}: {formatConsultaAmount(item.saldo)}
        </span>
        <span>
          {t('consultas.column.vencimiento')}: {formatConsultaDate(item.vencimiento)}
        </span>
      </div>
    </article>
  );
}

export function getDeudaDetailFields(): ConsultaDetailField<DeudaConsultaRow>[] {
  return [
    { labelKey: 'consultas.column.cliente', getValue: (item) => item.codCliente },
    { labelKey: 'consultas.column.razonSocial', getValue: (item) => item.razonSocial },
    { labelKey: 'consultas.column.tipo', getValue: (item) => item.tipo },
    { labelKey: 'consultas.column.numero', getValue: (item) => item.numero },
    { labelKey: 'consultas.column.fecha', getValue: (item) => formatConsultaDate(item.fecha) },
    { labelKey: 'consultas.column.vencimiento', getValue: (item) => formatConsultaDate(item.vencimiento) },
    { labelKey: 'consultas.column.saldo', getValue: (item) => formatConsultaAmount(item.saldo) },
  ];
}

export function renderChequeCard(item: ChequeConsultaRow, t: TFunction) {
  return (
    <article className="consultaKardexCard">
      <div className="consultaKardexCard__title">{item.numero}</div>
      <div className="consultaKardexCard__subtitle">{item.nombreCliente}</div>
      <div className="consultaKardexCard__metrics">
        <span>
          {t('consultas.column.importe')}: {formatConsultaAmount(item.importe)}
        </span>
        <span>
          {t('consultas.column.fecha')}: {formatConsultaDate(item.fecha)}
        </span>
      </div>
    </article>
  );
}

export function getChequeDetailFields(): ConsultaDetailField<ChequeConsultaRow>[] {
  return [
    { labelKey: 'consultas.column.numero', getValue: (item) => item.numero },
    { labelKey: 'consultas.column.cliente', getValue: (item) => item.codCliente },
    { labelKey: 'consultas.column.razonSocial', getValue: (item) => item.nombreCliente },
    { labelKey: 'consultas.column.banco', getValue: (item) => item.banco },
    { labelKey: 'consultas.column.fecha', getValue: (item) => formatConsultaDate(item.fecha) },
    { labelKey: 'consultas.column.importe', getValue: (item) => formatConsultaAmount(item.importe) },
    { labelKey: 'consultas.column.estado', getValue: (item) => item.estado },
    { labelKey: 'consultas.column.origen', getValue: (item) => item.origen },
  ];
}

export function renderHistorialCard(item: HistorialVentasRow) {
  return (
    <article className="consultaKardexCard">
      <div className="consultaKardexCard__title">{item.codArticulo}</div>
      <div className="consultaKardexCard__subtitle">{item.descripcion}</div>
      <div className="consultaKardexCard__metrics">
        <span>
          {item.tipo} {item.numero}
        </span>
        <span>{formatConsultaAmount(item.totSinImp)}</span>
      </div>
    </article>
  );
}

export function getHistorialDetailFields(): ConsultaDetailField<HistorialVentasRow>[] {
  return [
    { labelKey: 'consultas.column.cliente', getValue: (item) => item.codCliente },
    { labelKey: 'consultas.column.razonSocial', getValue: (item) => item.razonSocial },
    { labelKey: 'consultas.column.tipo', getValue: (item) => item.tipo },
    { labelKey: 'consultas.column.numero', getValue: (item) => item.numero },
    { labelKey: 'consultas.column.fecha', getValue: (item) => formatConsultaDate(item.fechaEmision) },
    { labelKey: 'consultas.column.codArticulo', getValue: (item) => item.codArticulo },
    { labelKey: 'consultas.column.descripcion', getValue: (item) => item.descripcion },
    { labelKey: 'consultas.column.cantidad', getValue: (item) => formatConsultaAmount(item.cantidad) },
    { labelKey: 'consultas.column.precio', getValue: (item) => formatConsultaAmount(item.precio) },
    { labelKey: 'consultas.column.importe', getValue: (item) => formatConsultaAmount(item.totSinImp) },
  ];
}

export function renderDetallePedidoCard(item: DetallePedidoConsultaRow) {
  return (
    <article className="consultaKardexCard">
      <div className="consultaKardexCard__title">
        {item.numero || item.codPedido}
      </div>
      <div className="consultaKardexCard__subtitle">{item.descripcionArticulo}</div>
      <div className="consultaKardexCard__metrics">
        <span>{item.codArticulo}</span>
        <span>{formatConsultaAmount(item.importeNeto)}</span>
      </div>
    </article>
  );
}

export function getDetallePedidoDetailFields(): ConsultaDetailField<DetallePedidoConsultaRow>[] {
  return [
    ...getComprobanteDetailFields(),
    { labelKey: 'consultas.column.codArticulo', getValue: (item) => item.codArticulo },
    { labelKey: 'consultas.column.descripcion', getValue: (item) => item.descripcionArticulo },
    { labelKey: 'consultas.column.cantidad', getValue: (item) => formatConsultaAmount(item.cantidad) },
    { labelKey: 'consultas.column.precio', getValue: (item) => formatConsultaAmount(item.precioNeto) },
    { labelKey: 'consultas.column.importe', getValue: (item) => formatConsultaAmount(item.importeNeto) },
  ];
}
