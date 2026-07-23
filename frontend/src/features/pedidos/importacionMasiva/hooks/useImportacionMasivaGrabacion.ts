import { useCallback, useState } from 'react';
import { useTranslation } from 'react-i18next';
import notify from 'devextreme/ui/notify';
import type { BorradorFila, ImportacionMasivaProgreso } from '../types/importacionMasivaTypes';
import { grabarLoteSecuencial } from '../utils/grabarLoteSecuencial';

type UseImportacionMasivaGrabacionParams = {
  onFilaOk: (idInterno: string) => void;
  onFilaError: (idInterno: string, errorMessage: string) => void;
};

export function useImportacionMasivaGrabacion({
  onFilaOk,
  onFilaError,
}: UseImportacionMasivaGrabacionParams) {
  const { t } = useTranslation();
  const [progreso, setProgreso] = useState<ImportacionMasivaProgreso | null>(null);
  const [isGrabando, setIsGrabando] = useState(false);

  const grabarLote = useCallback(
    async (filas: BorradorFila[]) => {
      if (filas.length === 0 || isGrabando) {
        return { ok: 0, err: 0 };
      }

      setIsGrabando(true);
      try {
        const resumen = await grabarLoteSecuencial(filas, t, {
          onProgreso: setProgreso,
          onFilaOk,
          onFilaError,
        });

        if (resumen.ok > 0 || resumen.err > 0) {
          notify({
            message: t('pedidos.importacionMasiva.resumenGrabacion', {
              ok: resumen.ok,
              err: resumen.err,
            }),
            type: resumen.err > 0 ? 'warning' : 'success',
            displayTime: resumen.err > 0 ? 5000 : 3000,
          });
        }

        return resumen;
      } finally {
        setIsGrabando(false);
        setProgreso(null);
      }
    },
    [isGrabando, onFilaError, onFilaOk, t],
  );

  return {
    progreso,
    isGrabando,
    grabarLote,
  };
}
