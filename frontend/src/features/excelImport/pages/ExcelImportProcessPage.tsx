import { useCallback, useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { fetchExcelImportProceso } from '../api/excelImportApi';
import { ExcelImportHostToolbar } from '../components/ExcelImportHostToolbar';
import type { ExcelImportHostResult } from '../types/excelImportHostTypes';
import './excelImportPages.css';

export function ExcelImportProcessPage() {
  const { codigoProceso = 'ARTICULOS_ALTA' } = useParams<{ codigoProceso: string }>();
  const { t } = useTranslation();
  const [nombreProceso, setNombreProceso] = useState<string | null>(null);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [lastResult, setLastResult] = useState<ExcelImportHostResult | null>(null);

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      try {
        const meta = await fetchExcelImportProceso(codigoProceso);
        if (mounted) {
          setNombreProceso(meta.nombreProceso);
        }
      } catch {
        if (mounted) {
          setLoadError(t('excelImport.procesoNotFound'));
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [codigoProceso, t]);

  const handleComplete = useCallback((result: ExcelImportHostResult) => {
    setLastResult(result);
  }, []);

  const title = useMemo(() => nombreProceso ?? codigoProceso, [codigoProceso, nombreProceso]);

  return (
    <main className="excelImportPage">
      <header className="excelImportPage__header">
        <h1>{title}</h1>
        {!loadError ? (
          <div className="excelImportPage__toolbar">
            <ExcelImportHostToolbar codigoProceso={codigoProceso} onComplete={handleComplete} />
          </div>
        ) : null}
      </header>

      {loadError ? <p>{loadError}</p> : null}

      {lastResult ? (
        <p className="excelImportPage__hostResult" data-testid="excelHostLastResult">
          {t('excelImport.hostLastResult', {
            validas: lastResult.validRows.length,
            errores: lastResult.meta.filasConError,
          })}
        </p>
      ) : (
        <p className="excelImportPage__hint">{t('excelImport.hostProcessHint')}</p>
      )}
    </main>
  );
}
