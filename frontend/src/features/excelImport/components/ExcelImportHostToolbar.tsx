import { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { fetchExcelImportProceso } from '../api/excelImportApi';
import { ExcelTemplateDownloadButton } from './ExcelTemplateDownloadButton';
import { ExcelImportHostModal } from './ExcelImportHostModal';
import type { ExcelImportHostResult, ExcelImportHostToolbarProps } from '../types/excelImportHostTypes';
import '../pages/excelImportPages.css';

export function ExcelImportHostToolbar({
  codigoProceso,
  disabled = false,
  onComplete,
  onCancel,
}: ExcelImportHostToolbarProps) {
  const { t } = useTranslation();
  const [excelImportAvailable, setExcelImportAvailable] = useState(false);
  const [generaPlantilla, setGeneraPlantilla] = useState(false);
  const [modalVisible, setModalVisible] = useState(false);

  useEffect(() => {
    let mounted = true;
    void fetchExcelImportProceso(codigoProceso)
      .then((meta) => {
        if (mounted) {
          setExcelImportAvailable(true);
          setGeneraPlantilla(meta.generaPlantilla);
        }
      })
      .catch(() => {
        if (mounted) {
          setExcelImportAvailable(false);
          setGeneraPlantilla(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, [codigoProceso]);

  const handleComplete = useCallback(
    (result: ExcelImportHostResult) => {
      setModalVisible(false);
      onComplete(result);
    },
    [onComplete],
  );

  const handleClose = useCallback(() => {
    setModalVisible(false);
    onCancel?.();
  }, [onCancel]);

  return (
    <div className="excelImportHostToolbar" data-testid="excelHostToolbar">
      <ExcelTemplateDownloadButton codigoProceso={codigoProceso} visible={generaPlantilla} disabled={disabled} />
      {excelImportAvailable ? (
        <div data-testid="excelHostImport">
          <Button
            text={t('excelImport.hostImport')}
            icon="upload"
            stylingMode="outlined"
            disabled={disabled}
            onClick={() => setModalVisible(true)}
          />
        </div>
      ) : null}
      <ExcelImportHostModal
        visible={modalVisible}
        codigoProceso={codigoProceso}
        onClose={handleClose}
        onComplete={handleComplete}
      />
    </div>
  );
}
