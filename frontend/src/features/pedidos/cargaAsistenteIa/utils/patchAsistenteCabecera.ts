import type { ComprobanteCabecera } from '../../types/comprobanteCabecera';
import { calcularBonificacionNeta } from '../../utils/renglonesCarga';

function coerceFieldValue(field: string, value: unknown): unknown {
  if (
    field === 'bonif1' ||
    field === 'bonif2' ||
    field === 'bonif3' ||
    field === 'nivel' ||
    field === 'codCondvta' ||
    field === 'listaPrecios' ||
    field === 'idDe' ||
    field === 'moneda'
  ) {
    if (value === null || value === undefined || value === '') {
      return field === 'nivel' || field === 'moneda' ? 0 : null;
    }
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  if (field === 'incluyeIva') {
    return Boolean(value);
  }

  if (
    field === 'expreso' ||
    field === 'expresoDire' ||
    field === 'codTranspor' ||
    field === 'codPerfil' ||
    field === 'listaPreciosDescripcion' ||
    field === 'direccionEntrega' ||
    field === 'fechaEntrega'
  ) {
    if (value === null || value === undefined) {
      return field === 'direccionEntrega' || field === 'listaPreciosDescripcion' ? '' : null;
    }
    const text = String(value).trim();
    if (field === 'direccionEntrega' || field === 'listaPreciosDescripcion') {
      return text;
    }
    return text === '' ? null : text;
  }

  return value;
}

/**
 * Aplica un patch de campos de cabecera desde acciones del asistente IA.
 */
export function patchAsistenteCabecera(
  current: ComprobanteCabecera,
  fields: Record<string, unknown>,
): ComprobanteCabecera {
  const next: ComprobanteCabecera = { ...current };

  for (const [field, value] of Object.entries(fields)) {
    const key = field.trim();
    if (key === '') {
      continue;
    }
    (next as Record<string, unknown>)[key] = coerceFieldValue(key, value);
  }

  if (
    Object.prototype.hasOwnProperty.call(fields, 'bonif1') ||
    Object.prototype.hasOwnProperty.call(fields, 'bonif2') ||
    Object.prototype.hasOwnProperty.call(fields, 'bonif3')
  ) {
    next.descuento = calcularBonificacionNeta(next.bonif1, next.bonif2, next.bonif3);
  }

  return next;
}
