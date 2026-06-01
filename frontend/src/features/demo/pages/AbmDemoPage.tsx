import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import CheckBox from 'devextreme-react/check-box';
import { Column } from 'devextreme-react/data-grid';
import TextBox from 'devextreme-react/text-box';
import {
  AbmFormPopup,
  abmFullPermissions,
  abmTestIds,
  confirmDelete,
  useAbmModal,
} from '../../../shared/ui/abm';
import { DataGridDx } from '../../../shared/ui/grids';

const abmDemoProceso = 'pw_demo_abm';
const abmDemoGridId = 'main';

export type AbmDemoItem = {
  id: number;
  code: string;
  name: string;
  active: boolean;
};

const initialItems: AbmDemoItem[] = [
  { id: 1, code: 'DEMO-001', name: 'Item Alpha', active: true },
  { id: 2, code: 'DEMO-002', name: 'Item Beta', active: false },
];

type AbmDemoFormState = {
  code: string;
  name: string;
  active: boolean;
};

const emptyFormState: AbmDemoFormState = {
  code: '',
  name: '',
  active: true,
};

function toFormState(record: AbmDemoItem | null): AbmDemoFormState {
  if (!record) {
    return { ...emptyFormState };
  }

  return {
    code: record.code,
    name: record.name,
    active: record.active,
  };
}

function getRecordLabel(row: AbmDemoItem): string {
  return row.code || row.name || String(row.id);
}

export function AbmDemoPage() {
  const { t } = useTranslation();
  const [items, setItems] = useState<AbmDemoItem[]>(initialItems);
  const [nextId, setNextId] = useState(3);
  const [formState, setFormState] = useState<AbmDemoFormState>(emptyFormState);
  const [formErrorKey, setFormErrorKey] = useState<string | null>(null);
  const abmModal = useAbmModal<AbmDemoItem>();

  const openCreateFlow = useCallback(() => {
    setFormErrorKey(null);
    setFormState(emptyFormState);
    abmModal.openCreate();
  }, [abmModal]);

  const openEditFlow = useCallback(
    (row: AbmDemoItem) => {
      setFormErrorKey(null);
      setFormState(toFormState(row));
      abmModal.openEdit(row);
    },
    [abmModal],
  );

  const handleDelete = useCallback(
    async (row: AbmDemoItem) => {
      const confirmed = await confirmDelete({
        recordLabel: getRecordLabel(row),
        t,
      });

      if (!confirmed) {
        return;
      }

      setItems((current) => current.filter((item) => item.id !== row.id));
    },
    [t],
  );

  const handleSave = useCallback(() => {
    const code = formState.code.trim();
    const name = formState.name.trim();

    if (!code || !name) {
      setFormErrorKey('abm.validation.required');
      return;
    }

    if (abmModal.mode === 'create') {
      const created: AbmDemoItem = {
        id: nextId,
        code,
        name,
        active: formState.active,
      };

      setItems((current) => [...current, created]);
      setNextId((current) => current + 1);
      abmModal.close();
      return;
    }

    if (abmModal.record) {
      setItems((current) =>
        current.map((item) =>
          item.id === abmModal.record?.id
            ? { ...item, code, name, active: formState.active }
            : item,
        ),
      );
      abmModal.close();
    }
  }, [abmModal, formState, nextId]);

  const abmConfig = useMemo(
    () => ({
      enabled: true as const,
      permissions: abmFullPermissions,
      onCreate: openCreateFlow,
      onEdit: openEditFlow,
      onDelete: (row: AbmDemoItem) => {
        void handleDelete(row);
      },
      getRecordLabel,
    }),
    [handleDelete, openCreateFlow, openEditFlow],
  );

  return (
    <section data-testid="process-demo-abm">
      <h2>{t('abm.demo.title')}</h2>
      <p>{t('abm.demo.description')}</p>

      <DataGridDx<AbmDemoItem>
        proceso={abmDemoProceso}
        gridId={abmDemoGridId}
        dataSource={items}
        keyExpr="id"
        abm={abmConfig}
      >
        <Column dataField="code" caption={t('abm.field.code')} />
        <Column dataField="name" caption={t('abm.field.name')} />
        <Column dataField="active" caption={t('abm.field.active')} dataType="boolean" />
      </DataGridDx>

      <AbmFormPopup
        isOpen={abmModal.isOpen}
        mode={abmModal.mode}
        errorKey={formErrorKey}
        onClose={abmModal.close}
        onSave={handleSave}
      >
        <TextBox
          value={formState.code}
          label={t('abm.field.code')}
          stylingMode="outlined"
          readOnly={abmModal.mode === 'view'}
          inputAttr={{
            'data-testid': abmTestIds.fieldCode,
            'aria-label': t('abm.field.code'),
          }}
          onValueChanged={(event) => {
            setFormState((current) => ({ ...current, code: String(event.value ?? '') }));
          }}
        />
        <TextBox
          value={formState.name}
          label={t('abm.field.name')}
          stylingMode="outlined"
          readOnly={abmModal.mode === 'view'}
          inputAttr={{
            'data-testid': abmTestIds.fieldName,
            'aria-label': t('abm.field.name'),
          }}
          onValueChanged={(event) => {
            setFormState((current) => ({ ...current, name: String(event.value ?? '') }));
          }}
        />
        <CheckBox
          value={formState.active}
          text={t('abm.field.active')}
          readOnly={abmModal.mode === 'view'}
          elementAttr={{ 'data-testid': 'abmFieldActive' }}
          onValueChanged={(event) => {
            setFormState((current) => ({ ...current, active: Boolean(event.value) }));
          }}
        />
      </AbmFormPopup>
    </section>
  );
}
