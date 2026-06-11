import type { PivotLayoutConfigurationJson } from '../../../features/pivotLayouts/model/pivotLayoutTypes';
import type dxPivotGrid from 'devextreme/ui/pivot_grid';

export type PivotGridBlockHandle = {
  captureConfiguration: () => PivotLayoutConfigurationJson | null;
  getPivotGridInstance: () => dxPivotGrid | null;
  isExportEmpty: () => boolean;
};
