import { useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate, useParams } from 'react-router-dom';
import Button from 'devextreme-react/button';
import CheckBox from 'devextreme-react/check-box';
import Toast from 'devextreme-react/toast';
import { Column } from 'devextreme-react/data-grid';
import { DataGridDx } from '../../../../shared/ui/grids';
import { ApiClientError } from '../../../../shared/http/client';
import {
  fetchRoleAttributes,
  saveRoleAttributes,
  type AdminRoleAttributeItem,
} from './rolesAdminApi';
import { AdminSecurityGate } from '../shared/AdminSecurityGate';

const adminRoleAttributesProceso = 'pw_adminroles_atributos';
const adminRoleAttributesGridId = 'main';

export function RoleAttributesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { rolId = '' } = useParams<{ rolId: string }>();
  const numericRolId = Number(rolId);

  const [rolNombre, setRolNombre] = useState('');
  const [readOnly, setReadOnly] = useState(false);
  const [items, setItems] = useState<AdminRoleAttributeItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [successToastVisible, setSuccessToastVisible] = useState(false);
  const [refreshToken, setRefreshToken] = useState(0);

  useEffect(() => {
    if (!Number.isFinite(numericRolId) || numericRolId <= 0) {
      setLoadError(t('admin.roles.loadError'));
      setIsLoading(false);
      return;
    }

    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      setLoadError(null);

      try {
        const response = await fetchRoleAttributes(numericRolId);

        if (!mounted) {
          return;
        }

        setRolNombre(response.rol.nombreRol);
        setReadOnly(response.readOnly);
        setItems(response.items.map((item) => ({ ...item })));
      } catch {
        if (mounted) {
          setItems([]);
          setLoadError(t('admin.roles.loadError'));
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [numericRolId, refreshToken, t]);

  const updateItemFlag = useCallback(
    (procedimiento: string, field: keyof Pick<AdminRoleAttributeItem, 'permisoAlta' | 'permisoBaja' | 'permisoModi' | 'permisoRepo'>, value: boolean) => {
      setItems((current) =>
        current.map((item) =>
          item.procedimiento === procedimiento ? { ...item, [field]: value } : item,
        ),
      );
    },
    [],
  );

  const handleSave = useCallback(async () => {
    if (readOnly) {
      return;
    }

    setIsSaving(true);
    setSaveError(null);

    try {
      await saveRoleAttributes(numericRolId, items);
      setRefreshToken((current) => current + 1);
      setSuccessToastVisible(true);
    } catch (error) {
      if (
        error instanceof ApiClientError &&
        error.respuestaKey === 'admin.roles.atributosAccesoTotalReadOnly'
      ) {
        setSaveError(t('admin.roles.atributosAccesoTotalMessage'));
      } else {
        setSaveError(t('admin.roles.saveError'));
      }
    } finally {
      setIsSaving(false);
    }
  }, [items, numericRolId, readOnly, t]);

  const toolbarEnd = useMemo(
    () => (
      <>
        <Button
          text={t('admin.roles.atributosSave')}
          type="default"
          disabled={readOnly || isSaving}
          onClick={() => {
            void handleSave();
          }}
          elementAttr={{ 'data-testid': 'roles.atributos.save' }}
        />
        <Button
          text={t('admin.roles.back')}
          stylingMode="outlined"
          onClick={() => {
            navigate('/admin/roles');
          }}
          elementAttr={{ 'data-testid': 'roles.atributos.back' }}
        />
      </>
    ),
    [handleSave, isSaving, navigate, readOnly, t],
  );

  return (
    <AdminSecurityGate>
      <section data-testid="roles.atributos">
        <h2>{t('admin.roles.atributosTitle', { rol: rolNombre })}</h2>

        {readOnly ? <p>{t('admin.roles.atributosAccesoTotalMessage')}</p> : null}
        {saveError ? (
          <p role="alert" data-testid="roles.atributos.error">
            {saveError}
          </p>
        ) : null}

        <DataGridDx<AdminRoleAttributeItem & Record<string, unknown>>
          proceso={adminRoleAttributesProceso}
          gridId={adminRoleAttributesGridId}
          dataSource={items}
          keyExpr="procedimiento"
          isLoading={isLoading}
          loadError={loadError}
          toolbarEnd={toolbarEnd}
          exportEnabled={false}
          enableGrouping={false}
        >
          <Column dataField="menuText" caption={t('admin.roles.atributosMenu')} />
          <Column
            dataField="permisoAlta"
            caption={t('admin.roles.atributosColumnAlta')}
            cellRender={({ data }) => (
              <CheckBox
                value={Boolean(data.permisoAlta)}
                readOnly={readOnly}
                elementAttr={{ 'data-testid': `roles.atributos.alta.${data.procedimiento}` }}
                onValueChanged={(event) => {
                  updateItemFlag(String(data.procedimiento), 'permisoAlta', Boolean(event.value));
                }}
              />
            )}
          />
          <Column
            dataField="permisoBaja"
            caption={t('admin.roles.atributosColumnBaja')}
            cellRender={({ data }) => (
              <CheckBox
                value={Boolean(data.permisoBaja)}
                readOnly={readOnly}
                elementAttr={{ 'data-testid': `roles.atributos.baja.${data.procedimiento}` }}
                onValueChanged={(event) => {
                  updateItemFlag(String(data.procedimiento), 'permisoBaja', Boolean(event.value));
                }}
              />
            )}
          />
          <Column
            dataField="permisoModi"
            caption={t('admin.roles.atributosColumnModi')}
            cellRender={({ data }) => (
              <CheckBox
                value={Boolean(data.permisoModi)}
                readOnly={readOnly}
                elementAttr={{ 'data-testid': `roles.atributos.modi.${data.procedimiento}` }}
                onValueChanged={(event) => {
                  updateItemFlag(String(data.procedimiento), 'permisoModi', Boolean(event.value));
                }}
              />
            )}
          />
          <Column
            dataField="permisoRepo"
            caption={t('admin.roles.atributosColumnRepo')}
            cellRender={({ data }) => (
              <CheckBox
                value={Boolean(data.permisoRepo)}
                readOnly={readOnly}
                elementAttr={{ 'data-testid': `roles.atributos.repo.${data.procedimiento}` }}
                onValueChanged={(event) => {
                  updateItemFlag(String(data.procedimiento), 'permisoRepo', Boolean(event.value));
                }}
              />
            )}
          />
        </DataGridDx>

        <Toast
          visible={successToastVisible}
          message={t('admin.roles.atributosSaveSuccess')}
          type="success"
          displayTime={4000}
          onHiding={() => setSuccessToastVisible(false)}
          elementAttr={{ 'data-testid': 'roles.atributos.success' }}
        />
      </section>
    </AdminSecurityGate>
  );
}
