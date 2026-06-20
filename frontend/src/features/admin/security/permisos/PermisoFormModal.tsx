import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import SelectBox from 'devextreme-react/select-box';
import TextBox from 'devextreme-react/text-box';
import { AbmFormPopup } from '../../../../shared/ui/abm';
import type { AbmModalMode } from '../../../../shared/ui/abm';
import type { AdminPermisoItem, AdminRoleLookupItem, AdminUsuarioLookupItem } from './permisosAdminApi';

export type PermisoFormState = {
  idUsuario: number | null;
  idRol: number | null;
  usuarioLabel: string;
};

type PermisoFormModalProps = {
  isOpen: boolean;
  mode: AbmModalMode;
  formState: PermisoFormState;
  isSaving: boolean;
  errorKey: string | null;
  usuarios: AdminUsuarioLookupItem[];
  roles: AdminRoleLookupItem[];
  onClose: () => void;
  onSave: () => void;
  onChange: (next: PermisoFormState) => void;
  onSearchUsuarios?: () => void;
};

function formatUsuarioOption(item: AdminUsuarioLookupItem | null): string {
  if (!item) {
    return '';
  }

  return `${item.codigo} - ${item.nameUser}`;
}

function formatRolOption(item: AdminRoleLookupItem | null): string {
  if (!item) {
    return '';
  }

  return item.nombreRol;
}

export function PermisoFormModal({
  isOpen,
  mode,
  formState,
  isSaving,
  errorKey,
  usuarios,
  roles,
  onClose,
  onSave,
  onChange,
  onSearchUsuarios,
}: PermisoFormModalProps) {
  const { t } = useTranslation();
  const isEdit = mode === 'edit';

  const usuarioItems = useMemo(() => usuarios, [usuarios]);
  const rolItems = useMemo(() => roles, [roles]);

  return (
    <AbmFormPopup
      isOpen={isOpen}
      mode={mode}
      isSaving={isSaving}
      errorKey={errorKey}
      onClose={onClose}
      onSave={onSave}
    >
      {isEdit ? (
        <TextBox
          value={formState.usuarioLabel}
          label={t('admin.permisos.usuario')}
          stylingMode="outlined"
          readOnly
          inputAttr={{
            'data-testid': 'permisosFieldUsuarioReadonly',
            'aria-label': t('admin.permisos.usuario'),
          }}
        />
      ) : (
        <SelectBox
          dataSource={usuarioItems}
          value={formState.idUsuario}
          valueExpr="id"
          displayExpr={formatUsuarioOption}
          searchEnabled
          minSearchLength={0}
          showClearButton
          label={t('admin.permisos.usuario')}
          elementAttr={{ 'data-testid': 'permisos.create' }}
          onValueChanged={(event) => {
            const selected = usuarioItems.find((item) => item.id === event.value) ?? null;
            onChange({
              ...formState,
              idUsuario: selected?.id ?? null,
              usuarioLabel: selected ? formatUsuarioOption(selected) : '',
            });
          }}
          onOpened={() => {
            onSearchUsuarios?.();
          }}
        />
      )}

      <SelectBox
        dataSource={rolItems}
        value={formState.idRol}
        valueExpr="id"
        displayExpr={formatRolOption}
        searchEnabled
        showClearButton
        label={t('admin.permisos.rol')}
        elementAttr={{ 'data-testid': 'permisosFieldRol' }}
        onValueChanged={(event) => {
          onChange({
            ...formState,
            idRol: typeof event.value === 'number' ? event.value : null,
          });
        }}
      />
    </AbmFormPopup>
  );
}

export function toPermisoFormState(record: AdminPermisoItem | null): PermisoFormState {
  if (!record) {
    return {
      idUsuario: null,
      idRol: null,
      usuarioLabel: '',
    };
  }

  return {
    idUsuario: record.idUsuario,
    idRol: record.idRol,
    usuarioLabel: `${record.usuarioCodigo} - ${record.usuarioNombre}`,
  };
}
