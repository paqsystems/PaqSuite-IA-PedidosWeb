import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ApiClientError } from '../../shared/http/client';
import { useAuth } from './AuthProvider';
import { changePasswordRequest } from './changePasswordApi';
import {
  validateChangePasswordForm,
  type ChangePasswordFormValues,
} from './changePasswordForm';
import { updateStoredSessionContext } from './authStorage';
import { authenticatedHomePath } from '../../app/router/protectedRoutes';

type ChangePasswordPageProps = {
  forceFirstLogin?: boolean;
};

const errorMessages: Record<string, string> = {
  'auth.invalidCurrentPassword': 'La contraseña actual no es correcta.',
  'auth.newPasswordSameAsCurrent': 'La nueva contraseña debe ser distinta a la actual.',
  'auth.passwordConfirmationMismatch': 'La confirmación no coincide con la nueva contraseña.',
  'auth.passwordPolicy': 'La nueva contraseña debe tener al menos 8 caracteres, una letra y un número.',
  'auth.passwordRequired': 'Complete todos los campos.',
  'auth.unauthenticated': 'Su sesión expiró. Inicie sesión nuevamente.',
  'auth.accountDisabled': 'Su cuenta no está habilitada para operar.',
  'validation.failed': 'Revise los datos ingresados.',
};

function resolveErrorMessage(errorKey: string): string {
  return errorMessages[errorKey] ?? 'No se pudo cambiar la contraseña.';
}

export function ChangePasswordPage({ forceFirstLogin = false }: ChangePasswordPageProps) {
  const navigate = useNavigate();
  const { sessionContext, setSessionContext } = useAuth();
  const [values, setValues] = useState<ChangePasswordFormValues>({
    currentPassword: '',
    newPassword: '',
    newPasswordConfirmation: '',
  });
  const [fieldErrors, setFieldErrors] = useState<ReturnType<typeof validateChangePasswordForm>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isFirstLoginGate = forceFirstLogin || sessionContext?.firstLogin === true;

  const canSubmit = !isSubmitting;

  function updateField(fieldName: keyof ChangePasswordFormValues, fieldValue: string) {
    setValues((previousValues) => ({
      ...previousValues,
      [fieldName]: fieldValue,
    }));
    setFieldErrors({});
    setFormError(null);
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const validationErrors = validateChangePasswordForm(values);
    setFieldErrors(validationErrors);

    if (Object.keys(validationErrors).length > 0) {
      return;
    }

    setIsSubmitting(true);
    setFormError(null);

    try {
      const envelope = await changePasswordRequest(values);
      setSessionContext(envelope.resultado);
      updateStoredSessionContext(envelope.resultado);
      navigate(authenticatedHomePath, { replace: true });
    } catch (error) {
      if (error instanceof ApiClientError) {
        if (error.status === 401) {
          navigate('/login', { replace: true });
          return;
        }

        setFormError(resolveErrorMessage(error.respuestaKey));
      } else {
        setFormError('No se pudo cambiar la contraseña.');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main>
      {isFirstLoginGate && (
        <p data-testid="first-login-gate">Debe cambiar su contraseña antes de continuar.</p>
      )}
      <h1>Cambiar contraseña</h1>
      <form data-testid="change-password-form" onSubmit={handleSubmit}>
        <label>
          Contraseña actual
          <input
            data-testid="changePasswordCurrent"
            name="currentPassword"
            type="password"
            value={values.currentPassword}
            onChange={(event) => updateField('currentPassword', event.target.value)}
            autoComplete="current-password"
          />
        </label>
        {fieldErrors.currentPassword && (
          <p data-testid="changePasswordError">{resolveErrorMessage(fieldErrors.currentPassword)}</p>
        )}

        <label>
          Nueva contraseña
          <input
            data-testid="changePasswordNew"
            name="newPassword"
            type="password"
            value={values.newPassword}
            onChange={(event) => updateField('newPassword', event.target.value)}
            autoComplete="new-password"
          />
        </label>
        {fieldErrors.newPassword && (
          <p data-testid="changePasswordError">{resolveErrorMessage(fieldErrors.newPassword)}</p>
        )}

        <label>
          Confirmar nueva contraseña
          <input
            data-testid="changePasswordConfirm"
            name="newPasswordConfirmation"
            type="password"
            value={values.newPasswordConfirmation}
            onChange={(event) => updateField('newPasswordConfirmation', event.target.value)}
            autoComplete="new-password"
          />
        </label>
        {fieldErrors.newPasswordConfirmation && (
          <p data-testid="changePasswordError">{resolveErrorMessage(fieldErrors.newPasswordConfirmation)}</p>
        )}

        <button type="submit" data-testid="changePasswordSubmit" disabled={!canSubmit}>
          Guardar contraseña
        </button>
      </form>
      {formError && <p data-testid="changePasswordError">{formError}</p>}
    </main>
  );
}
