import es from '../../locales/es.json';
import en from '../../locales/en.json';
import pt from '../../locales/pt.json';
import fr from '../../locales/fr.json';
import it from '../../locales/it.json';
import { isSupportedLocale } from './model/supportedLocales';

type LocaleCatalog = typeof es;

const catalogs: Record<string, LocaleCatalog> = {
  es,
  en,
  pt,
  fr,
  it,
};

export function getPivotDevExtremeMessageOverrides(localeCode: string): Record<string, string> {
  if (!isSupportedLocale(localeCode)) {
    return {};
  }

  const catalog = catalogs[localeCode];

  return {
    'dxPivotGrid-grandTotal': catalog['pivot.dx.grandTotal'],
    'dxPivotGrid-total': catalog['pivot.dx.total'],
    'dxPivotGrid-fieldChooserTitle': catalog['pivot.dx.fieldChooserTitle'],
    'dxPivotGrid-showFieldChooser': catalog['pivot.dx.showFieldChooser'],
    'dxPivotGrid-expandAll': catalog['pivot.dx.expandAll'],
    'dxPivotGrid-collapseAll': catalog['pivot.dx.collapseAll'],
    'dxPivotGrid-sortColumnBySummary': catalog['pivot.dx.sortColumnBySummary'],
    'dxPivotGrid-sortRowBySummary': catalog['pivot.dx.sortRowBySummary'],
    'dxPivotGrid-removeAllSorting': catalog['pivot.dx.removeAllSorting'],
    'dxPivotGrid-dataNotAvailable': catalog['pivot.dx.dataNotAvailable'],
  };
}
