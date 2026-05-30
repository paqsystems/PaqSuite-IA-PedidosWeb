import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
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

export function ChangePasswordPage({ forceFirstLogin = false }: ChangePasswordPageProps) {
  const navigate = useNavigate();
  const { t } = useTranslation();
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

  function resolveErrorMessage(errorKey: string): string {
    const translated = t(errorKey);

    if (translated !== errorKey) {
      return translated;
    }

    return t('changePassword.error.generic');
  }

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
        setFormError(t('changePassword.error.generic'));
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main>
      {isFirstLoginGate && (
        <p data-testid="first-login-gate">{t('changePassword.firstLoginGate')}</p>
      )}
      <h1>{t('changePassword.title')}</h1>
      <form data-testid="change-password-form" onSubmit={handleSubmit}>
        <label>
          {t('changePassword.current')}
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
          {t('changePassword.new')}
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
          {t('changePassword.confirm')}
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
          <p data-testid="changePasswordError">
            {resolveErrorMessage(fieldErrors.newPasswordConfirmation)}
          </p>
        )}

        <button type="submit" data-testid="changePasswordSubmit" disabled={!canSubmit}>
          {t('changePassword.submit')}
        </button>
      </form>
      {formError && <p data-testid="changePasswordError">{formError}</p>}
    </main>
  );
}
