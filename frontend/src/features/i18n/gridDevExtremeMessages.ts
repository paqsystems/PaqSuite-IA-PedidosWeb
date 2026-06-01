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

/**
 * Overrides DevExtreme DataGrid messages from app i18n (grid.dx.*).
 * Complementa `devextreme/localization/messages/{locale}.json`.
 */
export function getGridDevExtremeMessageOverrides(localeCode: string): Record<string, string> {
  if (!isSupportedLocale(localeCode)) {
    return {};
  }

  const catalog = catalogs[localeCode];

  return {
    'dxDataGrid-groupPanelEmptyText': catalog['grid.dx.groupPanelEmpty'],
    'dxDataGrid-columnChooserTitle': catalog['grid.dx.columnChooserTitle'],
    'dxDataGrid-columnChooserEmptyText': catalog['grid.dx.columnChooserEmpty'],
    'dxDataGrid-filterRowOperationEquals': catalog['grid.dx.filter.equals'],
    'dxDataGrid-filterRowOperationNotEquals': catalog['grid.dx.filter.notEquals'],
    'dxDataGrid-filterRowOperationLess': catalog['grid.dx.filter.less'],
    'dxDataGrid-filterRowOperationLessOrEquals': catalog['grid.dx.filter.lessOrEquals'],
    'dxDataGrid-filterRowOperationGreater': catalog['grid.dx.filter.greater'],
    'dxDataGrid-filterRowOperationGreaterOrEquals': catalog['grid.dx.filter.greaterOrEquals'],
    'dxDataGrid-filterRowOperationStartsWith': catalog['grid.dx.filter.startsWith'],
    'dxDataGrid-filterRowOperationContains': catalog['grid.dx.filter.contains'],
    'dxDataGrid-filterRowOperationNotContains': catalog['grid.dx.filter.notContains'],
    'dxDataGrid-filterRowOperationEndsWith': catalog['grid.dx.filter.endsWith'],
    'dxDataGrid-filterRowOperationBetween': catalog['grid.dx.filter.between'],
    'dxDataGrid-filterRowResetOperationText': catalog['grid.dx.filter.reset'],
    'dxDataGrid-sortingAscendingText': catalog['grid.dx.sort.ascending'],
    'dxDataGrid-sortingDescendingText': catalog['grid.dx.sort.descending'],
    'dxDataGrid-sortingClearText': catalog['grid.dx.sort.clear'],
    'dxDataGrid-groupHeaderText': catalog['grid.dx.group.byColumn'],
    'dxDataGrid-ungroupHeaderText': catalog['grid.dx.group.ungroup'],
    'dxDataGrid-ungroupAllText': catalog['grid.dx.group.ungroupAll'],
    'dxDataGrid-moveColumnToTheLeft': catalog['grid.dx.column.moveLeft'],
    'dxDataGrid-moveColumnToTheRight': catalog['grid.dx.column.moveRight'],
    'dxDataGrid-summaryCount': catalog['grid.dx.summary.count'],
    'dxDataGrid-summarySum': catalog['grid.dx.summary.sum'],
    'dxDataGrid-summaryMin': catalog['grid.dx.summary.min'],
    'dxDataGrid-summaryMax': catalog['grid.dx.summary.max'],
    'dxDataGrid-summaryAvg': catalog['grid.dx.summary.avg'],
  };
}
