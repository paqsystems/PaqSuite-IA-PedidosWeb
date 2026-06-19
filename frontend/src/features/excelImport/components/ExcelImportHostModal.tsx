import { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import FileUploader from 'devextreme-react/file-uploader';
import SelectBox from 'devextreme-react/select-box';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import { saveExcelWithPicker } from '../../../shared/ui/gridExport/saveExcelWithPicker';
import {
  buildHostResultFromLot,
  createExcelImportLot,
  downloadExcelErrorsExport,
  fetchExcelImportLot,
  fetchExcelValidRows,
  listExcelSheets,
  processExcelImportLot,
  resolveExcelImportErrorKey,
  type ExcelImportLotSummary,
} from '../api/excelImportApi';
import type { ExcelImportHostModalPhase, ExcelImportHostResult } from '../types/excelImportHostTypes';
import { ExcelImportErrorGrid } from './ExcelImportErrorGrid';

type ExcelImportHostModalProps = {
  visible: boolean;
  codigoProceso: string;
  onClose: () => void;
  onComplete: (result: ExcelImportHostResult) => void;
};

export function ExcelImportHostModal({
  visible,
  codigoProceso,
  onClose,
  onComplete,
}: ExcelImportHostModalProps) {
  const { t } = useTranslation();
  const [archivo, setArchivo] = useState<File | null>(null);
  const [hojas, setHojas] = useState<string[]>([]);
  const [hojaSeleccionada, setHojaSeleccionada] = useState<string | null>(null);
  const [phase, setPhase] = useState<ExcelImportHostModalPhase>('upload');
  const [isLoadingHojas, setIsLoadingHojas] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorKey, setErrorKey] = useState<string | null>(null);
  const [structuralMessage, setStructuralMessage] = useState<string | null>(null);
  const [activeLot, setActiveLot] = useState<ExcelImportLotSummary | null>(null);
  const [showContinue, setShowContinue] = useState(false);
  const prevVisibleRef = useRef(false);

  const resetUpload = useCallback(() => {
    setArchivo(null);
    setHojas([]);
    setHojaSeleccionada(null);
    setErrorKey(null);
    setStructuralMessage(null);
    setActiveLot(null);
    setShowContinue(false);
    setPhase('upload');
  }, []);

  useEffect(() => {
    if (visible && !prevVisibleRef.current) {
      resetUpload();
    }
    prevVisibleRef.current = visible;
  }, [visible, resetUpload]);

  const handleHidden = useCallback(() => {
    resetUpload();
    onClose();
  }, [onClose, resetUpload]);

  const handleFileChange = useCallback(
    async (file: File | null) => {
      setArchivo(file);
      setHojas([]);
      setHojaSeleccionada(null);
      setErrorKey(null);
      setStructuralMessage(null);

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

  const deliverValidRows = useCallback(
    async (lot: ExcelImportLotSummary) => {
      const items = await fetchExcelValidRows(lot.guidImportacion);
      const validRows = items.map((item) => item.datos);
      resetUpload();
      onComplete(buildHostResultFromLot(lot, validRows));
    },
    [onComplete, resetUpload],
  );

  const deliverEmpty = useCallback(
    (lot: ExcelImportLotSummary) => {
      resetUpload();
      onComplete(buildHostResultFromLot(lot, []));
    },
    [onComplete, resetUpload],
  );

  const runPostValidation = useCallback(
    async (lot: ExcelImportLotSummary) => {
      if (lot.estadoImportacion === 'con_error_estructura') {
        setStructuralMessage(lot.mensajeResultado ?? t('excelImport.structuralError'));
        setPhase('structuralError');
        return;
      }

      const hasRowErrors = lot.cantidadFilasConError > 0;
      const permiteParcial = lot.permiteProcesamientoParcial ?? false;
      const soloValidar = lot.permiteSoloValidar ?? false;

      if (!hasRowErrors) {
        if (soloValidar) {
          await deliverValidRows(lot);
          return;
        }

        setPhase('processing');
        await processExcelImportLot(lot.guidImportacion);
        const refreshed = await fetchExcelImportLot(lot.guidImportacion);
        await deliverValidRows(refreshed);
        return;
      }

      if (!permiteParcial) {
        setPhase('errors');
        return;
      }

      if (!soloValidar) {
        setPhase('processing');
        await processExcelImportLot(lot.guidImportacion);
        const refreshed = await fetchExcelImportLot(lot.guidImportacion);
        setActiveLot(refreshed);
        setShowContinue(true);
        setPhase('errors');
        return;
      }

      setShowContinue(true);
      setPhase('errors');
    },
    [deliverValidRows, t],
  );

  const handleSubmit = useCallback(async () => {
    if (!archivo || !hojaSeleccionada) {
      return;
    }

    setIsSubmitting(true);
    setErrorKey(null);
    try {
      const lot = await createExcelImportLot(codigoProceso, archivo, hojaSeleccionada);
      setActiveLot(lot);
      await runPostValidation(lot);
    } catch (error) {
      setErrorKey(resolveExcelImportErrorKey(error, 'excelImport.cargaError'));
    } finally {
      setIsSubmitting(false);
    }
  }, [archivo, codigoProceso, hojaSeleccionada, runPostValidation]);

  const handleExportErrors = useCallback(async () => {
    if (!activeLot) {
      return;
    }

    const { blob, fileName } = await downloadExcelErrorsExport(activeLot.guidImportacion);
    const buffer = await blob.arrayBuffer();
    await saveExcelWithPicker(buffer, fileName);
  }, [activeLot]);

  const modalWidth = phase === 'errors' ? 960 : 520;
  const modalHeight = phase === 'errors' ? 560 : 'auto';

  return (
    <Popup
      visible={visible}
      onHiding={handleHidden}
      title={t('excelImport.hostImportTitle')}
      width={modalWidth}
      height={modalHeight}
      showCloseButton={true}
      wrapperAttr={{ 'data-testid': 'excelHostImportModal' }}
    >
      {phase === 'upload' ? (
        <section className="excelImportHostModal__upload">
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
            <div className="excelImportHostModal__sheet" data-testid="excelSheetSelect">
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

          {errorKey ? <p className="excelImportHostModal__error">{t(errorKey)}</p> : null}
        </section>
      ) : null}

      {phase === 'structuralError' ? (
        <section className="excelImportHostModal__structural">
          <p>{structuralMessage}</p>
          <div className="excelImportHostModal__actions">
            <Button text={t('excelImport.hostClose')} stylingMode="outlined" onClick={handleHidden} />
            <div data-testid="excelHostRetry">
              <Button text={t('excelImport.hostRetry')} type="default" onClick={resetUpload} />
            </div>
          </div>
        </section>
      ) : null}

      {phase === 'processing' ? (
        <section className="excelImportHostModal__processing">
          <p>{t('excelImport.hostProcessing')}</p>
        </section>
      ) : null}

      {phase === 'errors' && activeLot ? (
        <section className="excelImportHostModal__errors">
          <p className="excelImportHostModal__summary">
            {t('excelImport.hostErrorSummary', {
              validas: activeLot.cantidadFilasValidas,
              errores: activeLot.cantidadFilasConError,
            })}
          </p>
          <ExcelImportErrorGrid guidImportacion={activeLot.guidImportacion} />
          <div className="excelImportHostModal__actions excelImportHostModal__actions--spread">
            <div className="excelImportHostModal__actionSlot excelImportHostModal__actionSlot--start">
              <div data-testid="excelHostExportErrors">
                <Button
                  text={t('excelImport.hostExportErrors')}
                  icon="export"
                  stylingMode="outlined"
                  onClick={() => void handleExportErrors()}
                />
              </div>
            </div>
            <div className="excelImportHostModal__actionSlot excelImportHostModal__actionSlot--center">
              <Button
                text={t('excelImport.hostClose')}
                stylingMode="outlined"
                onClick={() => deliverEmpty(activeLot)}
              />
            </div>
            <div className="excelImportHostModal__actionSlot excelImportHostModal__actionSlot--end">
              {showContinue ? (
                <div data-testid="excelHostContinue">
                  <Button
                    text={t('excelImport.hostContinue')}
                    type="default"
                    onClick={() => void deliverValidRows(activeLot)}
                  />
                </div>
              ) : null}
              <div data-testid="excelHostRetry">
                <Button text={t('excelImport.hostRetry')} type="default" onClick={resetUpload} />
              </div>
            </div>
          </div>
        </section>
      ) : null}
    </Popup>
  );
}
