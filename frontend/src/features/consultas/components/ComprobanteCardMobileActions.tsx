import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Tooltip from 'devextreme-react/tooltip';
import { getDataGridRowActionTestId } from '../../../shared/ui/grids';
import type { DataGridRowAction } from '../../../shared/ui/grids';
import type { ComprobanteConsultaRow } from '../api/consultaApi';

type ComprobanteCardMobileActionsProps = {
  row: ComprobanteConsultaRow;
  actions: DataGridRowAction<ComprobanteConsultaRow>[];
};

function resolveActionTargetId(row: ComprobanteConsultaRow, actionKey: string): string {
  return `comprobante-card-action-${row.id}-${actionKey}`;
}

function isRowActionVisible(
  action: DataGridRowAction<ComprobanteConsultaRow>,
  row: ComprobanteConsultaRow,
): boolean {
  if (typeof action.visible === 'function') {
    return action.visible(row);
  }

  return action.visible !== false;
}

function isRowActionDisabled(
  action: DataGridRowAction<ComprobanteConsultaRow>,
  row: ComprobanteConsultaRow,
): boolean {
  if (typeof action.disabled === 'function') {
    return action.disabled(row);
  }

  return action.disabled === true;
}

export function ComprobanteCardMobileActions({ row, actions }: ComprobanteCardMobileActionsProps) {
  const { t } = useTranslation();
  const visibleActions = actions.filter((action) => isRowActionVisible(action, row));

  if (visibleActions.length === 0) {
    return null;
  }

  return (
    <div
      className="consultaKardexCard__actions"
      data-testid="comprobante-card-actions"
      role="toolbar"
      aria-label={t('grid.column.actions')}
      onClick={(event) => {
        event.stopPropagation();
      }}
    >
      {visibleActions.map((action) => {
        const targetId = resolveActionTargetId(row, action.actionKey);
        const hint = t(action.hintKey);

        return (
          <span key={action.actionKey} className="consultaKardexCard__actionWrap">
            <Button
              id={targetId}
              icon={action.icon}
              stylingMode="text"
              hint={hint}
              disabled={isRowActionDisabled(action, row)}
              elementAttr={{
                'data-testid': getDataGridRowActionTestId(action.actionKey),
                'aria-label': hint,
              }}
              onClick={() => {
                action.onClick?.(row);
              }}
            />
            <Tooltip
              target={`#${targetId}`}
              showEvent="dxhoverstart dxclick dxhold"
              hideEvent="dxhoverend dxclick dxhold"
              position="top"
            >
              {hint}
            </Tooltip>
          </span>
        );
      })}
    </div>
  );
}
