import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { DataGridRef } from 'devextreme-react/data-grid';
import DropDownButton from 'devextreme-react/drop-down-button';
import { exportDataGridExcel } from '../exportDataGridExcel';
import { gridExportTestIds } from '../gridExportTestIds';
import type { ExportMode } from '../model/exportMode';
import { defaultExportMode } from '../model/exportMode';
import { buildSuggestedExportFileName } from '../utils/buildSuggestedExportFileName';
import './gridExportButton.css';

type GridExportButtonProps = {
  gridRef: React.RefObject<DataGridRef | null>;
  proceso: string;
  gridId?: string;
  exportEnabled?: boolean;
  isEmpty: boolean;
};

type ExportMenuItem = {
  id: ExportMode;
  text: string;
  testId: string;
};

export function GridExportButton({
  gridRef,
  proceso,
  gridId,
  exportEnabled = true,
  isEmpty,
}: GridExportButtonProps) {
  const { t, i18n } = useTranslation();
  const [isExporting, setIsExporting] = useState(false);

  const menuItems = useMemo<ExportMenuItem[]>(
    () => [
      {
        id: 'formatted',
        text: t('gridExport.mode.formatted'),
        testId: gridExportTestIds.modeFormatted,
      },
      {
        id: 'basic',
        text: t('gridExport.mode.basic'),
        testId: gridExportTestIds.modeBasic,
      },
    ],
    [t],
  );

  const handleExport = useCallback(
    async (mode: ExportMode = defaultExportMode) => {
      const gridInstance = gridRef.current?.instance();

      if (!gridInstance || isEmpty || !exportEnabled) {
        return;
      }

      setIsExporting(true);

      try {
        const fileName = buildSuggestedExportFileName(proceso, gridId);
        await exportDataGridExcel(gridInstance, mode, fileName, {
          locale: i18n.language,
          booleanLabels: {
            trueLabel: t('gridExport.boolean.true'),
            falseLabel: t('gridExport.boolean.false'),
          },
        });
      } finally {
        setIsExporting(false);
      }
    },
    [exportEnabled, gridId, gridRef, i18n.language, isEmpty, proceso, t],
  );

  if (!exportEnabled) {
    return null;
  }

  const disabled = isEmpty || isExporting;
  const hint = isEmpty ? t('gridExport.noData') : t('gridExport.hint');

  return (
    <DropDownButton
      className="gridExportButton"
      text={t('gridExport.action')}
      icon="exportxlsx"
      stylingMode="outlined"
      splitButton
      disabled={disabled}
      hint={hint}
      displayExpr="text"
      keyExpr="id"
      items={menuItems}
      dropDownOptions={{ width: 240 }}
      elementAttr={{ 'data-testid': gridExportTestIds.excel }}
      onButtonClick={() => {
        void handleExport(defaultExportMode);
      }}
      onItemClick={(event) => {
        const item = event.itemData as ExportMenuItem | undefined;
        void handleExport(item?.id ?? defaultExportMode);
      }}
      itemRender={(item: ExportMenuItem) => (
        <span data-testid={item.testId}>{item.text}</span>
      )}
    />
  );
}
