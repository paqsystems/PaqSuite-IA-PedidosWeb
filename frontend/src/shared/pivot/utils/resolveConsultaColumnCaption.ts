import type { TFunction } from 'i18next';

export function resolveConsultaColumnCaption(
  t: TFunction,
  dataField: string,
  fallback?: string,
): string {
  const key = `consultas.column.${dataField}`;
  const translated = t(key);

  return translated === key ? (fallback ?? dataField) : translated;
}
