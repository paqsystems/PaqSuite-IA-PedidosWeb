import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import SelectBox from 'devextreme-react/select-box';
import DataGrid, { Column, Selection } from 'devextreme-react/data-grid';
import { custom } from 'devextreme/ui/dialog';
import type { AdminRoleLookupItem, AdminUsuarioLookupItem } from './permisosAdminApi';

type PermisoBulkByUserModalProps = {
  visible: boolean;
  usuarios: AdminUsuarioLookupItem[];
  roles: AdminRoleLookupItem[];
  isSaving: boolean;
  onClose: () => void;
  onConfirm: (payload: { anchorId: number; rolIds: number[] }) => Promise<void>;
};

function formatUsuarioOption(item: AdminUsuarioLookupItem | null): string {
  if (!item) {
    return '';
  }

  return `${item.codigo} - ${item.nameUser}`;
}

export function PermisoBulkByUserModal({
  visible,
  usuarios,
  roles,
  isSaving,
  onClose,
  onConfirm,
}: PermisoBulkByUserModalProps) {
  const { t } = useTranslation();
  const [anchorId, setAnchorId] = useState<number | null>(null);
  const [selectedRolIds, setSelectedRolIds] = useState<number[]>([]);
  const [validationKey, setValidationKey] = useState<string | null>(null);

  const resetState = () => {
    setAnchorId(null);
    setSelectedRolIds([]);
    setValidationKey(null);
  };

  const handleConfirm = async () => {
    if (anchorId == null) {
      setValidationKey('admin.permisos.bulk.validationNoAnchor');
      return;
    }

    if (selectedRolIds.length === 0) {
      setValidationKey('admin.permisos.bulk.validationSinCombinaciones');
      return;
    }

    setValidationKey(null);

    const confirmed = await new Promise<boolean>((resolve) => {
      const dialog = custom({
        title: t('common.confirm'),
        messageHtml: t('admin.permisos.bulk.confirm', { count: selectedRolIds.length }),
        showTitle: true,
        dragEnabled: false,
        buttons: [
          {
            text: t('abm.cancel'),
            onClick: () => resolve(false),
          },
          {
            text: t('abm.save'),
            type: 'default',
            onClick: () => resolve(true),
            elementAttr: { 'data-testid': 'permisos.bulk.confirm' },
          },
        ],
      });

      dialog.show();
    });

    if (!confirmed) {
      return;
    }

    await onConfirm({ anchorId, rolIds: selectedRolIds });
    resetState();
  };

  const validationMessage = useMemo(() => {
    if (!validationKey) {
      return null;
    }

    return t(validationKey, { field: t('admin.permisos.usuario') });
  }, [t, validationKey]);

  return (
    <Popup
      visible={visible}
      onHiding={() => {
        if (!isSaving) {
          resetState();
          onClose();
        }
      }}
      showTitle
      title={t('admin.permisos.bulk.byUser')}
      width="min(720px, 96vw)"
      height="auto"
      maxHeight="90vh"
      wrapperAttr={{ 'data-testid': 'permisos.bulk.modal.byUser', class: 'permisosBulkModal' }}
    >
      <SelectBox
        dataSource={usuarios}
        value={anchorId}
        valueExpr="id"
        displayExpr={formatUsuarioOption}
        searchEnabled
        showClearButton
        label={t('admin.permisos.usuario')}
        elementAttr={{ 'data-testid': 'permisos.bulk.anchor.usuario' }}
        onValueChanged={(event) => {
          setAnchorId(typeof event.value === 'number' ? event.value : null);
        }}
      />

      <DataGrid
        dataSource={roles}
        keyExpr="id"
        showBorders
        height={320}
        selectedRowKeys={selectedRolIds}
        onSelectionChanged={(event) => {
          setSelectedRolIds((event.selectedRowKeys as number[]) ?? []);
        }}
        elementAttr={{ 'data-testid': 'permisos.bulk.grid.roles' }}
      >
        <Selection mode="multiple" showCheckBoxesMode="always" />
        <Column dataField="nombreRol" caption={t('admin.permisos.rol')} />
        <Column dataField="descripcionRol" caption={t('admin.roles.descripcion')} />
      </DataGrid>

      {validationMessage ? (
        <p role="alert" data-testid="permisos.bulk.validation">
          {validationMessage}
        </p>
      ) : null}

      <div className="permisosBulkModal__actions">
        <Button text={t('abm.cancel')} stylingMode="outlined" disabled={isSaving} onClick={onClose} />
        <Button
          text={t('abm.save')}
          type="default"
          disabled={isSaving}
          onClick={() => {
            void handleConfirm();
          }}
        />
      </div>
    </Popup>
  );
}
