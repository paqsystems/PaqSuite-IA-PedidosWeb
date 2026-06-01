import { useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { syncDevExtremeLocale } from '../../../../features/i18n/syncDevExtremeLocale';

export function useDataGridDevExtremeTexts() {
  const { t, i18n } = useTranslation();

  useEffect(() => {
    syncDevExtremeLocale(i18n.language);
  }, [i18n.language]);

  return useMemo(
    () => ({
      localeKey: i18n.language,
      groupPanelEmptyText: t('grid.dx.groupPanelEmpty'),
      columnChooserTitle: t('grid.dx.columnChooserTitle'),
      columnChooserEmptyText: t('grid.dx.columnChooserEmpty'),
      filterOperationDescriptions: {
        equal: t('grid.dx.filter.equals'),
        notEqual: t('grid.dx.filter.notEquals'),
        lessThan: t('grid.dx.filter.less'),
        lessThanOrEqual: t('grid.dx.filter.lessOrEquals'),
        greaterThan: t('grid.dx.filter.greater'),
        greaterThanOrEqual: t('grid.dx.filter.greaterOrEquals'),
        startsWith: t('grid.dx.filter.startsWith'),
        contains: t('grid.dx.filter.contains'),
        notContains: t('grid.dx.filter.notContains'),
        endsWith: t('grid.dx.filter.endsWith'),
        between: t('grid.dx.filter.between'),
      },
    }),
    [i18n.language, t],
  );
}
