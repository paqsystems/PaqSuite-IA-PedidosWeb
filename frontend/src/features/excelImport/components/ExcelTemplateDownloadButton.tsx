import { useCallback, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { saveExcelWithPicker } from '../../../shared/ui/gridExport/saveExcelWithPicker';
import { downloadExcelTemplate } from '../api/excelImportApi';

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

  const handleDownload = useCallback(async () => {
    setIsLoading(true);
    try {
      const blob = await downloadExcelTemplate(codigoProceso, i18n.language);
      const safeCodigo = codigoProceso.replace(/[^A-Za-z0-9_-]+/g, '_') || 'proceso';
      const suggestedName = `${safeCodigo}_plantilla.xlsx`;
      const buffer = await blob.arrayBuffer();
      await saveExcelWithPicker(buffer, suggestedName);
    } catch {
      // El shell muestra errores vía toast global si aplica.
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
    </div>
  );
}
