import type { PivotGridFieldConfig } from '../../../shared/pivot/utils/mapMetadataToPivotFields';

export type PivotLayoutListItem = {
  configId: number;
  nombre: string;
  createdByUserId: number;
  isOwner: boolean;
  updatedAt?: string | null;
};

export type PivotLayoutSelectItem = {
  configId: number | null;
  nombre: string;
};

export type PivotLayoutRestoreMode = 'pivotBase' | 'empty' | 'saved';

export type PivotLayoutConfigurationJson = {
  fields: PivotGridFieldConfig[];
};

export type PivotLayoutActive = {
  configId: number | null;
  nombre: string | null;
  configuracionJson: PivotLayoutConfigurationJson | null;
  versionDefinicionConsulta?: number;
  restoreMode: PivotLayoutRestoreMode;
};

export type PivotFieldLayoutState = {
  mode: PivotLayoutRestoreMode;
  configuracionJson: PivotLayoutConfigurationJson | null;
  version: number;
};
