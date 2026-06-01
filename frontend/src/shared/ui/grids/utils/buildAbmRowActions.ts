import { abmTestIds } from '../../abm/abmTestIds';
import type { AbmPermissions } from '../../abm/types/abmTypes';
import type { DataGridRowAction } from '../types/dataGridDxTypes';

export type DataGridDxAbmConfig<TRecord extends Record<string, unknown>> = {
  enabled: boolean;
  permissions: AbmPermissions;
  onCreate: () => void;
  onEdit: (row: TRecord) => void;
  onDelete: (row: TRecord) => void;
  getRecordLabel?: (row: TRecord) => string;
};

export function buildAbmRowActions<TRecord extends Record<string, unknown>>(
  abm: DataGridDxAbmConfig<TRecord>,
): DataGridRowAction<TRecord>[] {
  const actions: DataGridRowAction<TRecord>[] = [];

  if (abm.permissions.modi) {
    actions.push({
      actionKey: abmTestIds.edit,
      icon: 'edit',
      hintKey: 'abm.action.edit',
      onClick: (row) => abm.onEdit(row),
    });
  }

  if (abm.permissions.baja) {
    actions.push({
      actionKey: abmTestIds.delete,
      icon: 'trash',
      hintKey: 'abm.action.delete',
      onClick: (row) => abm.onDelete(row),
    });
  }

  return actions;
}

export function resolveAbmRowActionTestId(actionKey: string): string {
  if (actionKey === abmTestIds.edit || actionKey === abmTestIds.delete) {
    return actionKey;
  }

  return `dataGridRowAction-${actionKey}`;
}
