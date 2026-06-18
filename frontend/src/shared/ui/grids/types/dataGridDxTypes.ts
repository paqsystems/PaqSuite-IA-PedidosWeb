import type { ReactNode } from 'react';
import type { DataGridTypes } from 'devextreme-react/data-grid';
import type { RowPreparedEvent } from 'devextreme/ui/data_grid';
import type { DataGridDxAbmConfig } from '../utils/buildAbmRowActions';

export type DataGridDxSortingMode = 'single' | 'multiple';

export type DataGridRowAction<TRecord = Record<string, unknown>> = {
  actionKey: string;
  icon: string;
  hintKey: string;
  visible?: boolean | ((row: TRecord) => boolean);
  disabled?: boolean | ((row: TRecord) => boolean);
  onClick?: (row: TRecord) => void;
};

export type DataGridDxProps<TRecord extends Record<string, unknown> = Record<string, unknown>> = {
  proceso: string;
  gridId: string;
  dataSource: DataGridTypes.Properties['dataSource'];
  keyExpr?: string;
  children: ReactNode;
  rowActions?: DataGridRowAction<TRecord>[];
  toolbarStart?: ReactNode;
  toolbarEnd?: ReactNode;
  isLoading?: boolean;
  loadError?: string | null;
  emptyMessageKey?: string;
  sortingMode?: DataGridDxSortingMode;
  enableGrouping?: boolean;
  /** Footer con totalizadores configurables (clic derecho en celda de pie). Default: true. */
  enableSummary?: boolean;
  defaultPageSize?: number;
  /** Patrón ABM transversal (TR-GEN-03-patron-abm): botón + DX, acciones fila, callbacks modal. */
  abm?: DataGridDxAbmConfig<TRecord>;
  /** Exportación Excel en toolbar (TR-GEN-03-exportaciones). Default: true. */
  exportEnabled?: boolean;
  onRowPrepared?: (event: RowPreparedEvent) => void;
};
