import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../../shared/http/client';
import { useAuth } from './AuthProvider';
import { changePasswordRequest } from './changePasswordApi';
import {
  validateChangePasswordForm,
  type ChangePasswordFormValues,
} from './changePasswordForm';
import { updateStoredSessionContext } from './authStorage';
import { authenticatedHomePath } from '../../app/router/protectedRoutes';
import './ChangePasswordPage.css';

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

  function handleClose() {
    if (isFirstLoginGate || isSubmitting) {
      return;
    }

    navigate(authenticatedHomePath, { replace: true });
  }

  return (
    <main className="changePasswordPage">
      {isFirstLoginGate && (
        <p className="changePasswordPage__gate" data-testid="first-login-gate">
          {t('changePassword.firstLoginGate')}
        </p>
      )}

      <section className="changePasswordWindow" data-testid="changePasswordModal">
        <div className="changePasswordWindow__header">
          <h1 className="changePasswordWindow__title">{t('changePassword.title')}</h1>
        </div>

        <div className="changePasswordPage__content">
          <form className="changePasswordForm" data-testid="change-password-form" onSubmit={handleSubmit}>
            <label className="changePasswordField">
              <span className="changePasswordField__label">{t('changePassword.current')}</span>
              <TextBox
                className="changePasswordField__control"
                mode="password"
                value={values.currentPassword}
                stylingMode="outlined"
                inputAttr={{
                  name: 'currentPassword',
                  autoComplete: 'current-password',
                  'aria-label': t('changePassword.current'),
                  placeholder: t('changePassword.current'),
                  'data-testid': 'changePasswordCurrent',
                }}
                onValueChanged={(event) => updateField('currentPassword', String(event.value ?? ''))}
              />
              {fieldErrors.currentPassword && (
                <p className="changePasswordField__error" data-testid="changePasswordError">
                  {resolveErrorMessage(fieldErrors.currentPassword)}
                </p>
              )}
            </label>

            <label className="changePasswordField">
              <span className="changePasswordField__label">{t('changePassword.new')}</span>
              <TextBox
                className="changePasswordField__control"
                mode="password"
                value={values.newPassword}
                stylingMode="outlined"
                inputAttr={{
                  name: 'newPassword',
                  autoComplete: 'new-password',
                  'aria-label': t('changePassword.new'),
                  placeholder: t('changePassword.new'),
                  'data-testid': 'changePasswordNew',
                }}
                onValueChanged={(event) => updateField('newPassword', String(event.value ?? ''))}
              />
              {fieldErrors.newPassword && (
                <p className="changePasswordField__error" data-testid="changePasswordError">
                  {resolveErrorMessage(fieldErrors.newPassword)}
                </p>
              )}
            </label>

            <label className="changePasswordField">
              <span className="changePasswordField__label">{t('changePassword.confirm')}</span>
              <TextBox
                className="changePasswordField__control"
                mode="password"
                value={values.newPasswordConfirmation}
                stylingMode="outlined"
                inputAttr={{
                  name: 'newPasswordConfirmation',
                  autoComplete: 'new-password',
                  'aria-label': t('changePassword.confirm'),
                  placeholder: t('changePassword.confirm'),
                  'data-testid': 'changePasswordConfirm',
                }}
                onValueChanged={(event) => updateField('newPasswordConfirmation', String(event.value ?? ''))}
              />
              {fieldErrors.newPasswordConfirmation && (
                <p className="changePasswordField__error" data-testid="changePasswordError">
                  {resolveErrorMessage(fieldErrors.newPasswordConfirmation)}
                </p>
              )}
            </label>

            {formError && (
              <p className="changePasswordForm__error" data-testid="changePasswordError">
                {formError}
              </p>
            )}
          </form>

          <div className="changePasswordActions">
            {!isFirstLoginGate && (
              <Button
                stylingMode="outlined"
                text={t('changePassword.cancel')}
                disabled={isSubmitting}
                onClick={handleClose}
              />
            )}
            <Button
              type="default"
              text={isSubmitting ? t('changePassword.saving') : t('changePassword.submit')}
              disabled={isSubmitting}
              onClick={() => {
                const form = document.querySelector('[data-testid="change-password-form"]') as HTMLFormElement | null;
                form?.requestSubmit();
              }}
              elementAttr={{ 'data-testid': 'changePasswordSubmit' }}
            />
          </div>
        </div>
      </section>
    </main>
  );
}
