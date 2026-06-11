import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import DropDownButton from 'devextreme-react/drop-down-button';
import type { PivotMetadataResult } from '../../../shared/types/pivotMetadata';
import type { PivotGridBlockHandle } from '../../../shared/pivot/types/pivotGridBlockHandle';
import { pivotExportTestIds } from '../pivotExportTestIds';
import { buildPivotExportFileName } from '../utils/buildPivotExportFileName';
import { exportPivotGridExcel, type PivotExportMode } from '../utils/pivotExportExcel';
import {
  hasAnyPivotExportMode,
  resolvePivotExportFlags,
} from '../utils/resolvePivotExportFlags';
import './pivotExportButton.css';

type PivotExportButtonProps = {
  pivotGridRef: React.RefObject<PivotGridBlockHandle | null>;
  consultaId: string;
  metadata: PivotMetadataResult;
  exportEnabled?: boolean;
  isEmpty: boolean;
  userDisplayName?: string | null;
  activeLayoutName?: string | null;
  appliedFilters?: Record<string, unknown>;
  datasetTruncado?: boolean;
  onPivotTableLimited?: () => void;
};

type ExportMenuItem = {
  id: PivotExportMode;
  text: string;
  testId: string;
};

export function PivotExportButton({
  pivotGridRef,
  consultaId,
  metadata,
  exportEnabled = true,
  isEmpty,
  userDisplayName = null,
  activeLayoutName = null,
  appliedFilters = {},
  datasetTruncado = false,
  onPivotTableLimited,
}: PivotExportButtonProps) {
  const { t } = useTranslation();
  const [isExporting, setIsExporting] = useState(false);
  const exportFlags = useMemo(
    () => resolvePivotExportFlags(metadata.exportacion),
    [metadata.exportacion],
  );

  const menuItems = useMemo<ExportMenuItem[]>(() => {
    const items: ExportMenuItem[] = [];

    if (exportFlags.excelBasicoHabilitado) {
      items.push({
        id: 'basic',
        text: t('pivotExport.mode.basic'),
        testId: pivotExportTestIds.modeBasic,
      });
    }

    if (exportFlags.excelFormateadoHabilitado) {
      items.push({
        id: 'pivotTable',
        text: t('pivotExport.mode.pivotTable'),
        testId: pivotExportTestIds.modePivotTable,
      });
    }

    return items;
  }, [exportFlags.excelBasicoHabilitado, exportFlags.excelFormateadoHabilitado, t]);

  const handleExport = useCallback(
    async (mode: PivotExportMode) => {
      const pivotInstance = pivotGridRef.current?.getPivotGridInstance();

      if (!pivotInstance || isEmpty || !exportEnabled) {
        return;
      }

      setIsExporting(true);

      try {
        const exportedAt = new Date();
        const fileName = buildPivotExportFileName(consultaId, exportedAt);
        const result = await exportPivotGridExcel(pivotInstance, mode, fileName, {
          consultaId,
          userDisplayName,
          activeLayoutName,
          appliedFilters,
          exportacion: exportFlags,
          exportedAt,
          datasetTruncado,
          labels: {
            metaConsulta: t('pivotExport.metadata.consulta'),
            metaUsuario: t('pivotExport.metadata.usuario'),
            metaDiseno: t('pivotExport.metadata.diseno'),
            metaFecha: t('pivotExport.metadata.fecha'),
            metaFiltros: t('pivotExport.metadata.filtros'),
            metaTruncado: t('pivotExport.metadata.truncado'),
            initialTemplate: t('pivotLayout.initialTemplate'),
            metaYes: t('pivotExport.metadata.yes'),
            metaNo: t('pivotExport.metadata.no'),
          },
        });

        if (result.pivotTableLimited) {
          onPivotTableLimited?.();
        }
      } finally {
        setIsExporting(false);
      }
    },
    [
      activeLayoutName,
      appliedFilters,
      consultaId,
      datasetTruncado,
      exportEnabled,
      exportFlags,
      isEmpty,
      onPivotTableLimited,
      pivotGridRef,
      t,
      userDisplayName,
    ],
  );

  if (!exportEnabled || !hasAnyPivotExportMode(exportFlags)) {
    return null;
  }

  const disabled = isEmpty || isExporting;
  const hint = isEmpty ? t('pivotExport.noData') : t('pivotExport.hint');

  return (
    <DropDownButton
      className="pivotExportButton"
      text={t('pivotExport.action')}
      icon="exportxlsx"
      stylingMode="outlined"
      disabled={disabled}
      hint={hint}
      displayExpr="text"
      keyExpr="id"
      items={menuItems}
      dropDownOptions={{ width: 260 }}
      elementAttr={{ 'data-testid': pivotExportTestIds.export }}
      onItemClick={(event) => {
        const item = event.itemData as ExportMenuItem | undefined;

        if (item?.id) {
          void handleExport(item.id);
        }
      }}
      itemRender={(item: ExportMenuItem) => (
        <span data-testid={item.testId}>{item.text}</span>
      )}
    />
  );
}
