import type { TFunction } from 'i18next';
import type { ParametroConsultaRow } from '../api/parametrosConsultaApi';

function buildParametroKey(clave: string, suffix: 'caption' | 'tooltip'): string {
  return `parametros.pedidosWeb.${clave}.${suffix}`;
}

export function resolveParametroCaption(t: TFunction, clave: string, fallback: string): string {
  const key = buildParametroKey(clave, 'caption');

  return t(key, { defaultValue: fallback });
}

export function resolveParametroTooltip(t: TFunction, clave: string, fallback: string): string {
  const key = buildParametroKey(clave, 'tooltip');

  return t(key, { defaultValue: fallback });
}

export function resolveParametroValorMostrado(
  t: TFunction,
  row: ParametroConsultaRow,
): string {
  if (row.tipoValor === 'B') {
    const truthy = row.valorMostrado === 'true' || row.valorMostrado === '1';

    return truthy ? t('pedidos.carga.cabecera.si') : t('pedidos.carga.cabecera.no');
  }

  if (row.tipoValor === 'D' && row.valorMostrado) {
    const date = new Date(row.valorMostrado);

    if (!Number.isNaN(date.getTime())) {
      return date.toLocaleDateString();
    }
  }

  return row.valorMostrado;
}

export function mapParametroConsultaRow(t: TFunction, row: ParametroConsultaRow): ParametroConsultaRow {
  return {
    ...row,
    caption: resolveParametroCaption(t, row.clave, row.caption),
    tooltip: resolveParametroTooltip(t, row.clave, row.tooltip),
    valorMostrado: resolveParametroValorMostrado(t, row),
  };
}
