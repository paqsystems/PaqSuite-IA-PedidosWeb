import { useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ApiClientError } from '../../shared/http/client';
import { LocaleSelector } from '../i18n/components/LocaleSelector';
import { useCurrentLocale } from '../i18n/hooks/useCurrentLocale';
import { resetPasswordRequest } from './authApi';
import { validateResetPasswordForm, type ResetPasswordFormValues } from './passwordRecoveryForm';

export function ResetPasswordPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { t } = useTranslation();
  const { currentLocale, changeLocale } = useCurrentLocale();
  const [values, setValues] = useState<ResetPasswordFormValues>({
    token: searchParams.get('token') ?? '',
    newPassword: '',
    newPasswordConfirmation: '',
  });
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    setValues((previousValues) => ({
      ...previousValues,
      token: searchParams.get('token') ?? '',
    }));
  }, [searchParams]);

  function resolveMessage(messageKey: string): string {
    const translated = t(messageKey);

    if (translated !== messageKey) {
      return translated;
    }

    return t('auth.recovery.reset.genericError');
  }

  function updateField(fieldName: keyof ResetPasswordFormValues, fieldValue: string) {
    setValues((previousValues) => ({
      ...previousValues,
      [fieldName]: fieldValue,
    }));
    setFieldErrors({});
    setFormError(null);
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const validationErrors = validateResetPasswordForm(values);
    setFieldErrors(validationErrors);
    setFormError(null);

    if (Object.keys(validationErrors).length > 0) {
      return;
    }

    setIsSubmitting(true);

    try {
      const envelope = await resetPasswordRequest(values);
      navigate('/login?notice=passwordResetSuccess', { replace: true, state: { noticeKey: envelope.respuesta } });
    } catch (error) {
      if (error instanceof ApiClientError) {
        setFormError(resolveMessage(error.respuestaKey));
      } else {
        setFormError(t('auth.recovery.reset.genericError'));
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main>
      <LocaleSelector
        testId="localeSelectorResetPassword"
        value={currentLocale}
        onChange={(locale) => {
          void changeLocale(locale);
        }}
      />
      <h1>{t('auth.recovery.reset.title')}</h1>
      <p>{t('auth.recovery.reset.intro')}</p>
      <form data-testid="reset-password-form" onSubmit={handleSubmit}>
        <label>
          {t('auth.recovery.reset.newPassword')}
          <input
            data-testid="resetPasswordNew"
            name="newPassword"
            type="password"
            value={values.newPassword}
            onChange={(event) => updateField('newPassword', event.target.value)}
            autoComplete="new-password"
          />
        </label>
        {fieldErrors.newPassword && (
          <p data-testid="resetPasswordError">{resolveMessage(fieldErrors.newPassword)}</p>
        )}

        <label>
          {t('auth.recovery.reset.confirmPassword')}
          <input
            data-testid="resetPasswordConfirm"
            name="newPasswordConfirmation"
            type="password"
            value={values.newPasswordConfirmation}
            onChange={(event) => updateField('newPasswordConfirmation', event.target.value)}
            autoComplete="new-password"
          />
        </label>
        {fieldErrors.newPasswordConfirmation && (
          <p data-testid="resetPasswordError">
            {resolveMessage(fieldErrors.newPasswordConfirmation)}
          </p>
        )}

        {fieldErrors.token && <p data-testid="resetPasswordError">{resolveMessage(fieldErrors.token)}</p>}

        <button type="submit" data-testid="resetPasswordSubmit" disabled={isSubmitting}>
          {t('auth.recovery.reset.submit')}
        </button>
      </form>
      {formError && <p data-testid="resetPasswordError">{formError}</p>}
      <Link to="/login" data-testid="resetPasswordBackToLogin">
        {t('auth.recovery.reset.backToLogin')}
      </Link>
    </main>
  );
}
