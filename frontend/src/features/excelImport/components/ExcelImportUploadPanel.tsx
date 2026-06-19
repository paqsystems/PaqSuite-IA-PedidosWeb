import { useCallback, useState } from 'react';
import { useTranslation } from 'react-i18next';
import FileUploader from 'devextreme-react/file-uploader';
import SelectBox from 'devextreme-react/select-box';
import Button from 'devextreme-react/button';
import { createExcelImportLot, listExcelSheets, resolveExcelImportErrorKey } from '../api/excelImportApi';

type ExcelImportUploadPanelProps = {
  codigoProceso: string;
  onLotCreated: (guidImportacion: string) => void;
};

export function ExcelImportUploadPanel({ codigoProceso, onLotCreated }: ExcelImportUploadPanelProps) {
  const { t } = useTranslation();
  const [archivo, setArchivo] = useState<File | null>(null);
  const [hojas, setHojas] = useState<string[]>([]);
  const [hojaSeleccionada, setHojaSeleccionada] = useState<string | null>(null);
  const [isLoadingHojas, setIsLoadingHojas] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorKey, setErrorKey] = useState<string | null>(null);

  const handleFileChange = useCallback(
    async (file: File | null) => {
      setArchivo(file);
      setHojas([]);
      setHojaSeleccionada(null);
      setErrorKey(null);

      if (file === null) {
        return;
      }

      setIsLoadingHojas(true);
      try {
        const sheetNames = await listExcelSheets(codigoProceso, file);
        setHojas(sheetNames);
        setHojaSeleccionada(sheetNames[0] ?? null);
      } catch (error) {
        setErrorKey(resolveExcelImportErrorKey(error, 'excelImport.formatoInvalido'));
      } finally {
        setIsLoadingHojas(false);
      }
    },
    [codigoProceso],
  );

  const handleSubmit = useCallback(async () => {
    if (!archivo || !hojaSeleccionada) {
      return;
    }

    setIsSubmitting(true);
    setErrorKey(null);
    try {
      const lot = await createExcelImportLot(codigoProceso, archivo, hojaSeleccionada);
      onLotCreated(lot.guidImportacion);
    } catch (error) {
      setErrorKey(resolveExcelImportErrorKey(error, 'excelImport.cargaError'));
    } finally {
      setIsSubmitting(false);
    }
  }, [archivo, codigoProceso, hojaSeleccionada, onLotCreated]);

  return (
    <section className="excelImportUploadPanel">
      <div data-testid="excelFileUpload">
        <FileUploader
          accept=".xlsx"
          uploadMode="useForm"
          multiple={false}
          showFileList={true}
          labelText={t('excelImport.uploadLabel')}
          selectButtonText={t('excelImport.uploadSelect')}
          onValueChanged={(event) => {
            const files = event.value as File[] | null;
            void handleFileChange(files?.[0] ?? null);
          }}
        />
      </div>

      {hojas.length > 0 ? (
        <div className="excelImportUploadPanel__sheet" data-testid="excelSheetSelect">
          <SelectBox
            items={hojas}
            value={hojaSeleccionada}
            disabled={isLoadingHojas}
            onValueChanged={(event) => setHojaSeleccionada((event.value as string | null) ?? null)}
            label={t('excelImport.sheetLabel')}
          />
        </div>
      ) : null}

      <div data-testid="excelImportSubmit">
        <Button
          text={t('excelImport.submitUpload')}
          type="default"
          disabled={!archivo || !hojaSeleccionada || isSubmitting}
          onClick={() => void handleSubmit()}
        />
      </div>

      {errorKey ? <p className="excelImportUploadPanel__error">{t(errorKey)}</p> : null}
    </section>
  );
}
