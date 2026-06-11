import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

export type DxSelectBoxLoadState = {
  disabled: boolean;
  hint: string | undefined;
};

export function resolveDxSelectBoxLoadState(
  isLoading: boolean,
  loadingLabel: string,
  disableWhileLoading = true,
): DxSelectBoxLoadState {
  return {
    disabled: disableWhileLoading && isLoading,
    hint: isLoading ? loadingLabel : undefined,
  };
}

/**
 * Estado transversal para listas DevExtreme (SelectBox / Lookup) durante carga remota.
 * Contrato CC PQ #3: deshabilitar control y mostrar hint i18n mientras `isLoading`.
 */
export function useDxSelectBoxLoadState(
  isLoading: boolean,
  disableWhileLoading = true,
): DxSelectBoxLoadState {
  const { t } = useTranslation();

  return useMemo(
    () => resolveDxSelectBoxLoadState(isLoading, t('selectBox.loading'), disableWhileLoading),
    [disableWhileLoading, isLoading, t],
  );
}
