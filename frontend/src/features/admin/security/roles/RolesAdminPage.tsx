import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { Column } from 'devextreme-react/data-grid';
import {
  abmFullPermissions,
  confirmDelete,
  useAbmModal,
} from '../../../../shared/ui/abm';
import { DataGridDx } from '../../../../shared/ui/grids';
import { ApiClientError } from '../../../../shared/http/client';
import {
  createAdminRole,
  deleteAdminRole,
  fetchAdminRoles,
  updateAdminRole,
  type AdminRoleItem,
} from './rolesAdminApi';
import { RoleFormModal, type RoleFormState } from './RoleFormModal';
import { AdminSecurityGate } from '../shared/AdminSecurityGate';

const adminRolesProceso = 'pw_adminroles';
const adminRolesGridId = 'main';

const emptyFormState: RoleFormState = {
  nombreRol: '',
  descripcionRol: '',
  accesoTotal: false,
};

function toFormState(record: AdminRoleItem | null): RoleFormState {
  if (!record) {
    return { ...emptyFormState };
  }

  return {
    nombreRol: record.nombreRol,
    descripcionRol: record.descripcionRol,
    accesoTotal: record.accesoTotal,
  };
}

function getRecordLabel(row: AdminRoleItem): string {
  return row.nombreRol || String(row.id);
}

export function RolesAdminPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [items, setItems] = useState<AdminRoleItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [formState, setFormState] = useState<RoleFormState>(emptyFormState);
  const [formErrorKey, setFormErrorKey] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [refreshToken, setRefreshToken] = useState(0);
  const abmModal = useAbmModal<AdminRoleItem>();

  const reload = useCallback(async () => {
    setIsLoading(true);
    setLoadError(null);

    try {
      const roles = await fetchAdminRoles();
      setItems(roles);
    } catch {
      setItems([]);
      setLoadError(t('admin.roles.loadError'));
    } finally {
      setIsLoading(false);
    }
  }, [t]);

  useEffect(() => {
    void reload();
  }, [reload, refreshToken]);

  const openCreateFlow = useCallback(() => {
    setFormErrorKey(null);
    setFormState(emptyFormState);
    abmModal.openCreate();
  }, [abmModal]);

  const openEditFlow = useCallback(
    (row: AdminRoleItem) => {
      setFormErrorKey(null);
      setFormState(toFormState(row));
      abmModal.openEdit(row);
    },
    [abmModal],
  );

  const handleDelete = useCallback(
    async (row: AdminRoleItem) => {
      const confirmed = await confirmDelete({
        recordLabel: getRecordLabel(row),
        t,
      });

      if (!confirmed) {
        return;
      }

      try {
        await deleteAdminRole(row.id);
        setRefreshToken((current) => current + 1);
      } catch (error) {
        const messageKey =
          error instanceof ApiClientError && error.respuestaKey === 'admin.roles.deleteInUse'
            ? 'admin.roles.deleteInUse'
            : 'admin.roles.saveError';
        setLoadError(t(messageKey));
      }
    },
    [t],
  );

  const handleSave = useCallback(async () => {
    const nombreRol = formState.nombreRol.trim();

    if (!nombreRol) {
      setFormErrorKey('abm.validation.required');
      return;
    }

    setIsSaving(true);
    setFormErrorKey(null);

    try {
      if (abmModal.mode === 'create') {
        await createAdminRole({
          nombreRol,
          descripcionRol: formState.descripcionRol.trim(),
          accesoTotal: formState.accesoTotal,
        });
      } else if (abmModal.record) {
        await updateAdminRole(abmModal.record.id, {
          nombreRol,
          descripcionRol: formState.descripcionRol.trim(),
          accesoTotal: formState.accesoTotal,
        });
      }

      abmModal.close();
      setRefreshToken((current) => current + 1);
    } catch (error) {
      if (error instanceof ApiClientError && error.respuestaKey === 'admin.roles.duplicateRoleName') {
        setFormErrorKey('admin.roles.duplicateRoleName');
      } else {
        setFormErrorKey('admin.roles.saveError');
      }
    } finally {
      setIsSaving(false);
    }
  }, [abmModal, formState]);

  const rowActions = useMemo(
    () => [
      {
        actionKey: 'atributos',
        icon: 'key',
        hintKey: 'admin.roles.atributosHint',
        visible: (row: AdminRoleItem) => !row.accesoTotal,
        onClick: (row: AdminRoleItem) => {
          navigate(`/admin/roles/${row.id}/atributos`);
        },
      },
    ],
    [navigate],
  );

  const abmConfig = useMemo(
    () => ({
      enabled: true as const,
      permissions: abmFullPermissions,
      onCreate: openCreateFlow,
      onEdit: openEditFlow,
      onDelete: (row: AdminRoleItem) => {
        void handleDelete(row);
      },
      getRecordLabel,
    }),
    [handleDelete, openCreateFlow, openEditFlow],
  );

  return (
    <AdminSecurityGate>
      <section data-testid="roles.admin">
        <h2>{t('admin.roles.title')}</h2>

        <div data-testid="roles.grid">
        <DataGridDx<AdminRoleItem>
          proceso={adminRolesProceso}
          gridId={adminRolesGridId}
          dataSource={items}
          keyExpr="id"
          isLoading={isLoading}
          loadError={loadError}
          emptyMessageKey="admin.roles.empty"
          abm={abmConfig}
          rowActions={rowActions}
          exportEnabled={false}
        >
          <Column dataField="nombreRol" caption={t('admin.roles.nombre')} />
          <Column dataField="descripcionRol" caption={t('admin.roles.descripcion')} />
          <Column dataField="accesoTotal" caption={t('admin.roles.accesoTotal')} dataType="boolean" />
          <Column dataField="enUso" visible={false} />
        </DataGridDx>
        </div>

        <RoleFormModal
          isOpen={abmModal.isOpen}
          mode={abmModal.mode}
          formState={formState}
          isSaving={isSaving}
          errorKey={formErrorKey}
          onClose={abmModal.close}
          onSave={() => {
            void handleSave();
          }}
          onChange={setFormState}
        />
      </section>
    </AdminSecurityGate>
  );
}
