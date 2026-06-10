type UseDataGridDxStateParams = {
  isLoading?: boolean;
  loadError?: string | null;
};

export type DataGridDxViewState = 'loading' | 'error' | 'ready';

export function useDataGridDxState({
  isLoading = false,
  loadError = null,
}: UseDataGridDxStateParams): DataGridDxViewState {
  if (loadError) {
    return 'error';
  }

  if (isLoading) {
    return 'loading';
  }

  return 'ready';
}
