import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ApiClientError } from '../../shared/http/client';
import { LocaleSelector } from '../i18n/components/LocaleSelector';
import { useCurrentLocale } from '../i18n/hooks/useCurrentLocale';
import { forgotPasswordRequest } from './authApi';
import {
  validateForgotPasswordForm,
  type ForgotPasswordFormValues,
} from './passwordRecoveryForm';

export function ForgotPasswordPage() {
  const { t } = useTranslation();
  const { currentLocale, changeLocale } = useCurrentLocale();
  const [values, setValues] = useState<ForgotPasswordFormValues>({ email: '' });
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [successKey, setSuccessKey] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function resolveMessage(messageKey: string): string {
    const translated = t(messageKey);

    if (translated !== messageKey) {
      return translated;
    }

    return t('auth.recovery.forgot.genericError');
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const validationErrors = validateForgotPasswordForm(values);
    setFieldErrors(validationErrors);
    setFormError(null);
    setSuccessKey(null);

    if (Object.keys(validationErrors).length > 0) {
      return;
    }

    setIsSubmitting(true);

    try {
      const envelope = await forgotPasswordRequest({
        email: values.email.trim(),
        locale: currentLocale,
      });

      setValues({ email: '' });
      setSuccessKey(envelope.respuesta);
    } catch (error) {
      if (error instanceof ApiClientError) {
        setFormError(resolveMessage(error.respuestaKey));
      } else {
        setFormError(t('auth.recovery.forgot.genericError'));
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main>
      <LocaleSelector
        testId="localeSelectorForgotPassword"
        value={currentLocale}
        onChange={(locale) => {
          void changeLocale(locale);
        }}
      />
      <h1>{t('auth.recovery.forgot.title')}</h1>
      <p>{t('auth.recovery.forgot.intro')}</p>
      <form data-testid="forgot-password-form" onSubmit={handleSubmit}>
        <label>
          {t('auth.recovery.forgot.email')}
          <input
            data-testid="forgotPasswordEmail"
            name="email"
            type="email"
            value={values.email}
            onChange={(event) => {
              setValues({ email: event.target.value });
              setFieldErrors({});
              setFormError(null);
              setSuccessKey(null);
            }}
            autoComplete="email"
          />
        </label>
        {fieldErrors.email && (
          <p data-testid="forgotPasswordError">{resolveMessage(fieldErrors.email)}</p>
        )}
        <button type="submit" data-testid="forgotPasswordSubmit" disabled={isSubmitting}>
          {t('auth.recovery.forgot.submit')}
        </button>
      </form>
      {formError && <p data-testid="forgotPasswordError">{formError}</p>}
      {successKey && <p data-testid="forgotPasswordSuccess">{resolveMessage(successKey)}</p>}
      <Link to="/login" data-testid="forgotPasswordBackToLogin">
        {t('auth.recovery.forgot.backToLogin')}
      </Link>
    </main>
  );
}
