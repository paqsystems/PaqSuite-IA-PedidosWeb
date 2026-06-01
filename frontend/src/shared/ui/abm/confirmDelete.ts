import { custom } from 'devextreme/ui/dialog';
import type { TFunction } from 'i18next';
import { abmTestIds } from './abmTestIds';

export type ConfirmDeleteParams = {
  recordLabel: string;
  t: TFunction;
};

export function confirmDelete({ recordLabel, t }: ConfirmDeleteParams): Promise<boolean> {
  return new Promise((resolve) => {
    const dialog = custom({
      title: t('abm.delete.title'),
      messageHtml: t('abm.delete.message', { record: recordLabel }),
      showTitle: true,
      dragEnabled: false,
      buttons: [
        {
          text: t('abm.delete.cancel'),
          onClick: () => resolve(false),
          elementAttr: { 'data-testid': abmTestIds.cancelDelete },
        },
        {
          text: t('abm.delete.confirm'),
          type: 'danger',
          onClick: () => resolve(true),
          elementAttr: { 'data-testid': abmTestIds.confirmDelete },
        },
      ],
    });

    dialog.show();
  });
}
