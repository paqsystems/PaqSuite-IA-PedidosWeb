import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import SelectBox from 'devextreme-react/select-box';
import DataGrid, { Column, Selection } from 'devextreme-react/data-grid';
import { custom } from 'devextreme/ui/dialog';
import type { AdminRoleLookupItem, AdminUsuarioLookupItem } from './permisosAdminApi';

type PermisoBulkByRoleModalProps = {
  visible: boolean;
  usuarios: AdminUsuarioLookupItem[];
  roles: AdminRoleLookupItem[];
  isSaving: boolean;
  onClose: () => void;
  onConfirm: (payload: { anchorId: number; usuarioIds: number[] }) => Promise<void>;
};

function formatRolOption(item: AdminRoleLookupItem | null): string {
  if (!item) {
    return '';
  }

  return item.nombreRol;
}

export function PermisoBulkByRoleModal({
  visible,
  usuarios,
  roles,
  isSaving,
  onClose,
  onConfirm,
}: PermisoBulkByRoleModalProps) {
  const { t } = useTranslation();
  const [anchorId, setAnchorId] = useState<number | null>(null);
  const [selectedUsuarioIds, setSelectedUsuarioIds] = useState<number[]>([]);
  const [validationKey, setValidationKey] = useState<string | null>(null);

  const resetState = () => {
    setAnchorId(null);
    setSelectedUsuarioIds([]);
    setValidationKey(null);
  };

  const handleConfirm = async () => {
    if (anchorId == null) {
      setValidationKey('admin.permisos.bulk.validationNoAnchor');
      return;
    }

    if (selectedUsuarioIds.length === 0) {
      setValidationKey('admin.permisos.bulk.validationSinCombinaciones');
      return;
    }

    setValidationKey(null);

    const confirmed = await new Promise<boolean>((resolve) => {
      const dialog = custom({
        title: t('common.confirm'),
        messageHtml: t('admin.permisos.bulk.confirm', { count: selectedUsuarioIds.length }),
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

    await onConfirm({ anchorId, usuarioIds: selectedUsuarioIds });
    resetState();
  };

  const validationMessage = useMemo(() => {
    if (!validationKey) {
      return null;
    }

    return t(validationKey, { field: t('admin.permisos.rol') });
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
      title={t('admin.permisos.bulk.byRole')}
      width="min(720px, 96vw)"
      height="auto"
      maxHeight="90vh"
      wrapperAttr={{ 'data-testid': 'permisos.bulk.modal.byRole', class: 'permisosBulkModal' }}
    >
      <SelectBox
        dataSource={roles}
        value={anchorId}
        valueExpr="id"
        displayExpr={formatRolOption}
        searchEnabled
        showClearButton
        label={t('admin.permisos.rol')}
        elementAttr={{ 'data-testid': 'permisos.bulk.anchor.rol' }}
        onValueChanged={(event) => {
          setAnchorId(typeof event.value === 'number' ? event.value : null);
        }}
      />

      <DataGrid
        dataSource={usuarios}
        keyExpr="id"
        showBorders
        height={320}
        selectedRowKeys={selectedUsuarioIds}
        onSelectionChanged={(event) => {
          setSelectedUsuarioIds((event.selectedRowKeys as number[]) ?? []);
        }}
        elementAttr={{ 'data-testid': 'permisos.bulk.grid.usuarios' }}
      >
        <Selection mode="multiple" showCheckBoxesMode="always" />
        <Column
          dataField="codigo"
          caption={t('admin.permisos.usuario')}
          calculateCellValue={(row: AdminUsuarioLookupItem) => `${row.codigo} - ${row.nameUser}`}
        />
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
