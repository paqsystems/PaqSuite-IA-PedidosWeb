import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import SelectBox from 'devextreme-react/select-box';
import { Column } from 'devextreme-react/data-grid';
import Button from 'devextreme-react/button';
import { abmFullPermissions, confirmDelete, useAbmModal } from '../../../../shared/ui/abm';
import { DataGridDx } from '../../../../shared/ui/grids';
import { ApiClientError } from '../../../../shared/http/client';
import { AdminSecurityGate } from '../shared/AdminSecurityGate';
import {
  createAdminPermiso,
  createAdminPermisoBatch,
  deleteAdminPermiso,
  fetchAdminPermisos,
  fetchAdminRolesLookup,
  lookupAdminUsuarios,
  updateAdminPermiso,
  type AdminPermisoItem,
  type AdminRoleLookupItem,
  type AdminUsuarioLookupItem,
} from './permisosAdminApi';
import { PermisoBulkByRoleModal } from './PermisoBulkByRoleModal';
import { PermisoBulkByUserModal } from './PermisoBulkByUserModal';
import { PermisoFormModal, toPermisoFormState, type PermisoFormState } from './PermisoFormModal';

const adminPermisosProceso = 'pw_adminpermisos';
const adminPermisosGridId = 'main';

const emptyFormState: PermisoFormState = {
  idUsuario: null,
  idRol: null,
  usuarioLabel: '',
};

function getRecordLabel(row: AdminPermisoItem): string {
  return `${row.usuarioCodigo} / ${row.rolNombre}`;
}

export function PermisosAdminPage() {
  const { t } = useTranslation();
  const [items, setItems] = useState<AdminPermisoItem[]>([]);
  const [usuarios, setUsuarios] = useState<AdminUsuarioLookupItem[]>([]);
  const [roles, setRoles] = useState<AdminRoleLookupItem[]>([]);
  const [filterUsuarioId, setFilterUsuarioId] = useState<number | null>(null);
  const [filterRolId, setFilterRolId] = useState<number | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [formState, setFormState] = useState<PermisoFormState>(emptyFormState);
  const [formErrorKey, setFormErrorKey] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [refreshToken, setRefreshToken] = useState(0);
  const [bulkByUserOpen, setBulkByUserOpen] = useState(false);
  const [bulkByRoleOpen, setBulkByRoleOpen] = useState(false);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const abmModal = useAbmModal<AdminPermisoItem>();

  const loadLookups = useCallback(async () => {
    try {
      const [usuariosLookup, rolesLookup] = await Promise.all([
        lookupAdminUsuarios(undefined, 1, 50),
        fetchAdminRolesLookup(),
      ]);

      setUsuarios(usuariosLookup.items);
      setRoles(rolesLookup);
    } catch {
      setLoadError(t('admin.permisos.loadOptionsError'));
    }
  }, [t]);

  const reload = useCallback(async () => {
    setIsLoading(true);
    setLoadError(null);

    try {
      const permisos = await fetchAdminPermisos(filterUsuarioId, filterRolId);
      setItems(permisos);
    } catch {
      setItems([]);
      setLoadError(t('admin.permisos.loadError'));
    } finally {
      setIsLoading(false);
    }
  }, [filterRolId, filterUsuarioId, t]);

  useEffect(() => {
    void loadLookups();
  }, [loadLookups]);

  useEffect(() => {
    void reload();
  }, [reload, refreshToken]);

  const openCreateFlow = useCallback(() => {
    setFormErrorKey(null);
    setFormState(emptyFormState);
    void loadLookups();
    abmModal.openCreate();
  }, [abmModal, loadLookups]);

  const openEditFlow = useCallback(
    (row: AdminPermisoItem) => {
      setFormErrorKey(null);
      setFormState(toPermisoFormState(row));
      abmModal.openEdit(row);
    },
    [abmModal],
  );

  const handleDelete = useCallback(
    async (row: AdminPermisoItem) => {
      const confirmed = await confirmDelete({
        recordLabel: getRecordLabel(row),
        t,
      });

      if (!confirmed) {
        return;
      }

      try {
        await deleteAdminPermiso(row.id);
        setRefreshToken((current) => current + 1);
      } catch {
        setLoadError(t('admin.permisos.deleteError'));
      }
    },
    [t],
  );

  const handleSave = useCallback(async () => {
    if (formState.idRol == null) {
      setFormErrorKey('abm.validation.required');
      return;
    }

    if (abmModal.mode === 'create' && formState.idUsuario == null) {
      setFormErrorKey('abm.validation.required');
      return;
    }

    setIsSaving(true);
    setFormErrorKey(null);

    try {
      if (abmModal.mode === 'create') {
        await createAdminPermiso({
          idUsuario: formState.idUsuario as number,
          idRol: formState.idRol,
        });
      } else if (abmModal.record) {
        await updateAdminPermiso(abmModal.record.id, { idRol: formState.idRol });
      }

      abmModal.close();
      setRefreshToken((current) => current + 1);
    } catch (error) {
      if (error instanceof ApiClientError && error.respuestaKey === 'admin.permisos.duplicateAssignment') {
        setFormErrorKey('admin.permisos.duplicateAssignment');
      } else {
        setFormErrorKey('admin.permisos.saveError');
      }
    } finally {
      setIsSaving(false);
    }
  }, [abmModal, formState]);

  const handleBatchSuccess = useCallback(
    (result: { creados: number; omitidos: number }) => {
      setSuccessMessage(
        t('admin.permisos.bulk.successMessage', {
          creados: result.creados,
          omitidos: result.omitidos,
        }),
      );
      setBulkByUserOpen(false);
      setBulkByRoleOpen(false);
      setRefreshToken((current) => current + 1);
    },
    [t],
  );

  const runBatchByUser = useCallback(
    async (payload: { anchorId: number; rolIds: number[] }) => {
      setIsSaving(true);

      try {
        const result = await createAdminPermisoBatch({
          mode: 'by_user',
          anchorId: payload.anchorId,
          rolIds: payload.rolIds,
        });
        handleBatchSuccess(result);
      } catch {
        setLoadError(t('admin.permisos.saveError'));
      } finally {
        setIsSaving(false);
      }
    },
    [handleBatchSuccess, t],
  );

  const runBatchByRole = useCallback(
    async (payload: { anchorId: number; usuarioIds: number[] }) => {
      setIsSaving(true);

      try {
        const result = await createAdminPermisoBatch({
          mode: 'by_role',
          anchorId: payload.anchorId,
          usuarioIds: payload.usuarioIds,
        });
        handleBatchSuccess(result);
      } catch {
        setLoadError(t('admin.permisos.saveError'));
      } finally {
        setIsSaving(false);
      }
    },
    [handleBatchSuccess, t],
  );

  const toolbarStart = useMemo(
    () => (
      <>
        <SelectBox
          dataSource={usuarios}
          value={filterUsuarioId}
          valueExpr="id"
          displayExpr={(item: AdminUsuarioLookupItem | null) =>
            item ? `${item.codigo} - ${item.nameUser}` : ''
          }
          searchEnabled
          showClearButton
          width={260}
          label={t('admin.permisos.filterUsuario')}
          onValueChanged={(event) => {
            setFilterUsuarioId(typeof event.value === 'number' ? event.value : null);
            setRefreshToken((current) => current + 1);
          }}
        />
        <SelectBox
          dataSource={roles}
          value={filterRolId}
          valueExpr="id"
          displayExpr="nombreRol"
          searchEnabled
          showClearButton
          width={220}
          label={t('admin.permisos.filterRol')}
          onValueChanged={(event) => {
            setFilterRolId(typeof event.value === 'number' ? event.value : null);
            setRefreshToken((current) => current + 1);
          }}
        />
      </>
    ),
    [filterRolId, filterUsuarioId, roles, t, usuarios],
  );

  const toolbarEnd = useMemo(
    () => (
      <>
        <Button
          text={t('admin.permisos.bulk.byUser')}
          stylingMode="outlined"
          onClick={() => {
            void loadLookups();
            setBulkByUserOpen(true);
          }}
          elementAttr={{ 'data-testid': 'permisos.bulk.byUser' }}
        />
        <Button
          text={t('admin.permisos.bulk.byRole')}
          stylingMode="outlined"
          onClick={() => {
            void loadLookups();
            setBulkByRoleOpen(true);
          }}
          elementAttr={{ 'data-testid': 'permisos.bulk.byRole' }}
        />
      </>
    ),
    [loadLookups, t],
  );

  const abmConfig = useMemo(
    () => ({
      enabled: true as const,
      permissions: abmFullPermissions,
      onCreate: openCreateFlow,
      onEdit: openEditFlow,
      onDelete: (row: AdminPermisoItem) => {
        void handleDelete(row);
      },
      getRecordLabel,
    }),
    [handleDelete, openCreateFlow, openEditFlow],
  );

  return (
    <AdminSecurityGate>
      <section data-testid="permisos.admin">
        <h2>{t('admin.permisos.title')}</h2>

        {successMessage ? <p data-testid="permisos.bulk.success">{successMessage}</p> : null}

        <div data-testid="permisos.grid">
          <DataGridDx<AdminPermisoItem>
            proceso={adminPermisosProceso}
            gridId={adminPermisosGridId}
            dataSource={items}
            keyExpr="id"
            isLoading={isLoading}
            loadError={loadError}
            emptyMessageKey="admin.permisos.empty"
            abm={abmConfig}
            toolbarStart={toolbarStart}
            toolbarEnd={toolbarEnd}
            exportEnabled={false}
          >
            <Column dataField="usuarioCodigo" caption={t('admin.permisos.usuario')} />
            <Column dataField="usuarioNombre" caption={t('admin.permisos.nombreUsuario')} />
            <Column dataField="rolNombre" caption={t('admin.permisos.rol')} />
          </DataGridDx>
        </div>

        <PermisoFormModal
          isOpen={abmModal.isOpen}
          mode={abmModal.mode}
          formState={formState}
          isSaving={isSaving}
          errorKey={formErrorKey}
          usuarios={usuarios}
          roles={roles}
          onClose={abmModal.close}
          onSave={() => {
            void handleSave();
          }}
          onChange={setFormState}
          onSearchUsuarios={() => {
            void loadLookups();
          }}
        />

        <PermisoBulkByUserModal
          visible={bulkByUserOpen}
          usuarios={usuarios}
          roles={roles}
          isSaving={isSaving}
          onClose={() => {
            setBulkByUserOpen(false);
          }}
          onConfirm={runBatchByUser}
        />

        <PermisoBulkByRoleModal
          visible={bulkByRoleOpen}
          usuarios={usuarios}
          roles={roles}
          isSaving={isSaving}
          onClose={() => {
            setBulkByRoleOpen(false);
          }}
          onConfirm={runBatchByRole}
        />
      </section>
    </AdminSecurityGate>
  );
}
