import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ApiClientError } from '../../shared/http/client';
import { LocaleSelector } from '../i18n/components/LocaleSelector';
import { useCurrentLocale } from '../i18n/hooks/useCurrentLocale';
import { loginRequest } from './authApi';
import { persistAuthSession } from './authStorage';
import type { SessionContext } from './types';

type LoginPageProps = {
  onLoginSuccess: (sessionContext: SessionContext) => void;
};

export function LoginPage({ onLoginSuccess }: LoginPageProps) {
  const navigate = useNavigate();
  const { t } = useTranslation();
  const { currentLocale, changeLocale } = useCurrentLocale();
  const [codigo, setCodigo] = useState('');
  const [password, setPassword] = useState('');
  const [errorKey, setErrorKey] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setErrorKey(null);
    setIsSubmitting(true);

    try {
      const envelope = await loginRequest({ codigo, password });
      const { token, ...sessionContext } = envelope.resultado;

      persistAuthSession(token, envelope.resultado);

      onLoginSuccess(envelope.resultado);

      if (sessionContext.firstLogin) {
        navigate('/change-password', { replace: true });
        return;
      }

      navigate('/dashboard', { replace: true });
    } catch (error) {
      if (error instanceof ApiClientError) {
        setErrorKey(error.respuestaKey);
      } else {
        setErrorKey('auth.invalidCredentials');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main>
      <LocaleSelector
        testId="localeSelectorLogin"
        value={currentLocale}
        onChange={(locale) => {
          void changeLocale(locale);
        }}
      />
      <h1>{t('login.title')}</h1>
      <form data-testid="login-form" onSubmit={handleSubmit}>
        <label>
          {t('login.username')}
          <input
            name="codigo"
            value={codigo}
            onChange={(event) => setCodigo(event.target.value)}
            autoComplete="username"
          />
        </label>
        <label>
          {t('login.password')}
          <input
            name="password"
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            autoComplete="current-password"
          />
        </label>
        <button type="submit" data-testid="login-submit" disabled={isSubmitting}>
          {t('login.submit')}
        </button>
      </form>
      {errorKey === 'auth.invalidCredentials' && (
        <p data-testid="auth-error-generic">{t('auth.invalidCredentials')}</p>
      )}
      {errorKey === 'auth.noCommercialProfile' && (
        <p data-testid="auth-error-no-commercial-profile">
          {t('auth.noCommercialProfile')}
        </p>
      )}
      {errorKey === 'auth.noPermission' && (
        <p data-testid="auth-error-no-permission">{t('auth.noPermission')}</p>
      )}
      {errorKey === 'tenant.invalid' && (
        <p data-testid="auth-error-tenant">{t('tenant.invalid')}</p>
      )}
    </main>
  );
}
