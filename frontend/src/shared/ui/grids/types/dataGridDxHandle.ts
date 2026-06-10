export type DataGridDxHandle = {
  captureState: () => Record<string, unknown> | null;
  applyState: (state: Record<string, unknown> | null) => void;
  getVisibleRowCount: () => number;
};
