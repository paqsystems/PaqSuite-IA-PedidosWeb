import { useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../../shared/http/client';
import { LocaleSelector } from '../i18n/components/LocaleSelector';
import { useCurrentLocale } from '../i18n/hooks/useCurrentLocale';
import { forgotPasswordRequest } from './authApi';
import {
  validateForgotPasswordForm,
  type ForgotPasswordFormValues,
} from './passwordRecoveryForm';
import './ForgotPasswordPage.css';

export function ForgotPasswordPage() {
  const { t } = useTranslation();
  const { currentLocale, changeLocale } = useCurrentLocale();
  const formRef = useRef<HTMLFormElement | null>(null);
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
    <main className="forgotPasswordPage">
      <section className="forgotPasswordPage__panel">
        <div className="forgotPasswordCard">
          <div className="forgotPasswordCard__locale">
            <LocaleSelector
              testId="localeSelectorForgotPassword"
              value={currentLocale}
              onChange={(locale) => {
                void changeLocale(locale);
              }}
            />
          </div>

          <div className="forgotPasswordCard__header">
            <h1 className="forgotPasswordCard__title">{t('auth.recovery.forgot.title')}</h1>
            <p className="forgotPasswordCard__description">{t('auth.recovery.forgot.intro')}</p>
          </div>

          <form
            ref={formRef}
            className="forgotPasswordForm"
            data-testid="forgot-password-form"
            onSubmit={handleSubmit}
          >
            <label className="forgotPasswordField">
              <span className="forgotPasswordField__label">{t('auth.recovery.forgot.email')}</span>
              <TextBox
                className="forgotPasswordField__input"
                value={values.email}
                stylingMode="outlined"
                inputAttr={{
                  'data-testid': 'forgotPasswordEmail',
                  name: 'email',
                  type: 'email',
                  autoComplete: 'email',
                  placeholder: t('auth.recovery.forgot.email'),
                }}
                onValueChanged={(event) => {
                  setValues({ email: String(event.value ?? '') });
                  setFieldErrors({});
                  setFormError(null);
                  if (event.event) {
                    setSuccessKey(null);
                  }
                }}
              />
            </label>

            {fieldErrors.email && (
              <p className="forgotPasswordMessage forgotPasswordMessage--error" data-testid="forgotPasswordError">
                {resolveMessage(fieldErrors.email)}
              </p>
            )}

            {formError && (
              <p className="forgotPasswordMessage forgotPasswordMessage--error" data-testid="forgotPasswordError">
                {formError}
              </p>
            )}

            {successKey && (
              <p className="forgotPasswordMessage forgotPasswordMessage--success" data-testid="forgotPasswordSuccess">
                {resolveMessage(successKey)}
              </p>
            )}

            <Button
              className="forgotPasswordForm__submit"
              type="default"
              disabled={isSubmitting}
              text={t('auth.recovery.forgot.submit')}
              elementAttr={{ 'data-testid': 'forgotPasswordSubmit' }}
              onClick={() => {
                formRef.current?.requestSubmit();
              }}
            />
          </form>

          <Link className="forgotPasswordCard__link" to="/login" data-testid="forgotPasswordBackToLogin">
            {t('auth.recovery.forgot.backToLogin')}
          </Link>
        </div>
      </section>
    </main>
  );
}
