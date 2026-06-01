import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../../shared/http/client';
import { LocaleSelector } from '../i18n/components/LocaleSelector';
import { useCurrentLocale } from '../i18n/hooks/useCurrentLocale';
import { isSupportedLocale } from '../i18n/model/supportedLocales';
import { resetPasswordRequest } from './authApi';
import { validateResetPasswordForm, type ResetPasswordFormValues } from './passwordRecoveryForm';
import './ResetPasswordPage.css';

export function ResetPasswordPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { t } = useTranslation();
  const { currentLocale, changeLocale } = useCurrentLocale();
  const formRef = useRef<HTMLFormElement | null>(null);
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

  useEffect(() => {
    const requestedLocale = searchParams.get('locale');

    if (requestedLocale === null || !isSupportedLocale(requestedLocale) || requestedLocale === currentLocale) {
      return;
    }

    void changeLocale(requestedLocale);
  }, [changeLocale, currentLocale, searchParams]);

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
    <main className="resetPasswordPage">
      <section className="resetPasswordPage__panel">
        <div className="resetPasswordCard">
          <div className="resetPasswordCard__locale">
            <LocaleSelector
              testId="localeSelectorResetPassword"
              value={currentLocale}
              onChange={(locale) => {
                void changeLocale(locale);
              }}
            />
          </div>

          <div className="resetPasswordCard__header">
            <h1 className="resetPasswordCard__title">{t('auth.recovery.reset.title')}</h1>
            <p className="resetPasswordCard__description">{t('auth.recovery.reset.intro')}</p>
          </div>

          <form
            ref={formRef}
            className="resetPasswordForm"
            data-testid="reset-password-form"
            onSubmit={handleSubmit}
          >
            <label className="resetPasswordField">
              <span className="resetPasswordField__label">{t('auth.recovery.reset.newPassword')}</span>
              <TextBox
                className="resetPasswordField__input"
                mode="password"
                value={values.newPassword}
                stylingMode="outlined"
                inputAttr={{
                  'data-testid': 'resetPasswordNew',
                  name: 'newPassword',
                  autoComplete: 'new-password',
                  'aria-label': t('auth.recovery.reset.newPassword'),
                  placeholder: t('auth.recovery.reset.newPassword'),
                }}
                onValueChanged={(event) => updateField('newPassword', String(event.value ?? ''))}
              />
            </label>
            {fieldErrors.newPassword && (
              <p className="resetPasswordMessage resetPasswordMessage--error" data-testid="resetPasswordError">
                {resolveMessage(fieldErrors.newPassword)}
              </p>
            )}

            <label className="resetPasswordField">
              <span className="resetPasswordField__label">{t('auth.recovery.reset.confirmPassword')}</span>
              <TextBox
                className="resetPasswordField__input"
                mode="password"
                value={values.newPasswordConfirmation}
                stylingMode="outlined"
                inputAttr={{
                  'data-testid': 'resetPasswordConfirm',
                  name: 'newPasswordConfirmation',
                  autoComplete: 'new-password',
                  'aria-label': t('auth.recovery.reset.confirmPassword'),
                  placeholder: t('auth.recovery.reset.confirmPassword'),
                }}
                onValueChanged={(event) => updateField('newPasswordConfirmation', String(event.value ?? ''))}
              />
            </label>
            {fieldErrors.newPasswordConfirmation && (
              <p className="resetPasswordMessage resetPasswordMessage--error" data-testid="resetPasswordError">
                {resolveMessage(fieldErrors.newPasswordConfirmation)}
              </p>
            )}

            {fieldErrors.token && (
              <p className="resetPasswordMessage resetPasswordMessage--error" data-testid="resetPasswordError">
                {resolveMessage(fieldErrors.token)}
              </p>
            )}

            {formError && (
              <p className="resetPasswordMessage resetPasswordMessage--error" data-testid="resetPasswordError">
                {formError}
              </p>
            )}

            <Button
              className="resetPasswordForm__submit"
              type="default"
              disabled={isSubmitting}
              text={t('auth.recovery.reset.submit')}
              elementAttr={{ 'data-testid': 'resetPasswordSubmit' }}
              onClick={() => {
                formRef.current?.requestSubmit();
              }}
            />
          </form>

          <Link className="resetPasswordCard__link" to="/login" data-testid="resetPasswordBackToLogin">
            {t('auth.recovery.reset.backToLogin')}
          </Link>
        </div>
      </section>
    </main>
  );
}
