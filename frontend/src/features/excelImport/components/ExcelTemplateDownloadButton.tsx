import { useCallback, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { saveExcelWithPickerLazy } from '../../../shared/ui/gridExport/saveExcelWithPicker';
import { downloadExcelTemplate, resolveExcelImportErrorKey } from '../api/excelImportApi';

type ExcelTemplateDownloadButtonProps = {
  codigoProceso: string;
  visible?: boolean;
  disabled?: boolean;
};

export function ExcelTemplateDownloadButton({
  codigoProceso,
  visible = true,
  disabled = false,
}: ExcelTemplateDownloadButtonProps) {
  const { t, i18n } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [errorKey, setErrorKey] = useState<string | null>(null);

  const handleDownload = useCallback(async () => {
    setIsLoading(true);
    setErrorKey(null);
    try {
      const safeCodigo = codigoProceso.replace(/[^A-Za-z0-9_-]+/g, '_') || 'proceso';
      const suggestedName = `${safeCodigo}_plantilla.xlsx`;
      await saveExcelWithPickerLazy(suggestedName, async () => {
        const blob = await downloadExcelTemplate(codigoProceso, i18n.language);
        return blob.arrayBuffer();
      });
    } catch (error) {
      setErrorKey(resolveExcelImportErrorKey(error, 'excelImport.plantillaNoDisponible'));
    } finally {
      setIsLoading(false);
    }
  }, [codigoProceso, i18n.language]);

  if (!visible) {
    return null;
  }

  return (
    <div data-testid="excelTemplateDownload">
      <Button
        text={t('excelImport.downloadTemplate')}
        icon="download"
        stylingMode="outlined"
        disabled={disabled || isLoading}
        onClick={() => void handleDownload()}
      />
      {errorKey ? (
        <p className="excelImportHostToolbar__error" role="alert" data-testid="excelTemplateDownloadError">
          {t(errorKey)}
        </p>
      ) : null}
    </div>
  );
}
