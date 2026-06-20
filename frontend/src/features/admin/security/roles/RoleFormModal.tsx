import { useTranslation } from 'react-i18next';
import CheckBox from 'devextreme-react/check-box';
import TextBox from 'devextreme-react/text-box';
import { AbmFormPopup } from '../../../../shared/ui/abm';
import type { AbmModalMode } from '../../../../shared/ui/abm';

export type RoleFormState = {
  nombreRol: string;
  descripcionRol: string;
  accesoTotal: boolean;
};

type RoleFormModalProps = {
  isOpen: boolean;
  mode: AbmModalMode;
  formState: RoleFormState;
  isSaving: boolean;
  errorKey: string | null;
  onClose: () => void;
  onSave: () => void;
  onChange: (next: RoleFormState) => void;
};

export function RoleFormModal({
  isOpen,
  mode,
  formState,
  isSaving,
  errorKey,
  onClose,
  onSave,
  onChange,
}: RoleFormModalProps) {
  const { t } = useTranslation();
  const isReadOnly = mode === 'view';

  return (
    <AbmFormPopup
      isOpen={isOpen}
      mode={mode}
      isSaving={isSaving}
      errorKey={errorKey}
      onClose={onClose}
      onSave={onSave}
    >
      <TextBox
        value={formState.nombreRol}
        label={t('admin.roles.nombre')}
        stylingMode="outlined"
        readOnly={isReadOnly}
        inputAttr={{
          'data-testid': 'rolesFieldNombre',
          'aria-label': t('admin.roles.nombre'),
        }}
        onValueChanged={(event) => {
          onChange({ ...formState, nombreRol: String(event.value ?? '') });
        }}
      />
      <TextBox
        value={formState.descripcionRol}
        label={t('admin.roles.descripcion')}
        stylingMode="outlined"
        readOnly={isReadOnly}
        inputAttr={{
          'data-testid': 'rolesFieldDescripcion',
          'aria-label': t('admin.roles.descripcion'),
        }}
        onValueChanged={(event) => {
          onChange({ ...formState, descripcionRol: String(event.value ?? '') });
        }}
      />
      <CheckBox
        value={formState.accesoTotal}
        text={t('admin.roles.accesoTotal')}
        readOnly={isReadOnly}
        elementAttr={{ 'data-testid': 'rolesFieldAccesoTotal' }}
        onValueChanged={(event) => {
          onChange({ ...formState, accesoTotal: Boolean(event.value) });
        }}
      />
    </AbmFormPopup>
  );
}
