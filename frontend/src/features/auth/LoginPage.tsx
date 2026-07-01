import { useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../../shared/http/client';
import { isNativeApp } from '../../shared/platform/isNativeApp';
import { getLastTenantForLogin, setActiveTenant } from '../../shared/mobile/mobileRuntime';
import { isValidTenantSlug, normalizeTenant } from '../../shared/mobile/normalizeTenant';
import { getAuthenticatedHomePath } from '../mobile/mobileNavigation';
import { MobileConfigButton } from '../mobile/MobileConfigButton';
import { LocaleSelector } from '../i18n/components/LocaleSelector';
import { useCurrentLocale } from '../i18n/hooks/useCurrentLocale';
import { loginRequest } from './authApi';
import { persistAuthSession } from './authStorage';
import { useAuth } from './AuthProvider';
import type { SessionContext } from './types';
import './LoginPage.css';

type LoginPageProps = {
  onLoginSuccess: (sessionContext: SessionContext) => void;
};

export function LoginPage({ onLoginSuccess }: LoginPageProps) {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const { t } = useTranslation();
  const { expiredReasonKey, clearExpiredReason } = useAuth();
  const { currentLocale, changeLocale } = useCurrentLocale();
  const nativeApp = isNativeApp();
  const [tenant, setTenant] = useState('');
  const [codigo, setCodigo] = useState('');
  const [password, setPassword] = useState('');
  const [errorKey, setErrorKey] = useState<string | null>(null);
  const [noticeKey, setNoticeKey] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (!nativeApp) {
      return;
    }

    let isCancelled = false;

    async function preloadTenant() {
      const lastTenant = await getLastTenantForLogin();
      if (!isCancelled && lastTenant) {
        setTenant(lastTenant);
      }
    }

    void preloadTenant();

    return () => {
      isCancelled = true;
    };
  }, [nativeApp]);

  useEffect(() => {
    if (expiredReasonKey) {
      setErrorKey(expiredReasonKey);
      clearExpiredReason();
    }
  }, [clearExpiredReason, expiredReasonKey]);

  useEffect(() => {
    if (searchParams.get('notice') === 'passwordResetSuccess') {
      setNoticeKey('auth.passwordResetSuccess');
      setSearchParams({}, { replace: true });
    }
  }, [searchParams, setSearchParams]);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setErrorKey(null);
    setNoticeKey(null);

    if (nativeApp) {
      const normalizedTenant = normalizeTenant(tenant);

      if (!isValidTenantSlug(normalizedTenant)) {
        setErrorKey('tenant.invalid');
        return;
      }

      await setActiveTenant(normalizedTenant);
    }

    setIsSubmitting(true);

    try {
      const envelope = await loginRequest({ codigo, password });
      const { token, ...sessionContext } = envelope.resultado;

      await persistAuthSession(token, envelope.resultado);

      onLoginSuccess(envelope.resultado);

      if (sessionContext.firstLogin) {
        navigate('/change-password', { replace: true });
        return;
      }

      navigate(getAuthenticatedHomePath(), { replace: true });
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
    <main className="loginPage">
      <section className="loginPage__hero">
        <div className="loginPage__heroContent">
          <span className="loginPage__badge">{t('shell.footer.brand')}</span>
          <h1 className="loginPage__title">{t('login.title')}</h1>
          <p className="loginPage__subtitle">{t('login.subtitle')}</p>
        </div>
      </section>

      <section className="loginPage__panel">
        <div className="loginCard">
          <div className="loginCard__toolbar">
            <div className="loginCard__locale">
              <LocaleSelector
                testId="localeSelectorLogin"
                value={currentLocale}
                onChange={(locale) => {
                  void changeLocale(locale);
                }}
              />
            </div>
            {nativeApp && <MobileConfigButton tenantForHealthCheck={tenant} />}
          </div>

          <div className="loginCard__header">
            <h2 className="loginCard__heading">{t('login.welcome')}</h2>
            <p className="loginCard__description">{t('login.hint')}</p>
          </div>

          {noticeKey === 'auth.passwordResetSuccess' && (
            <p className="loginMessage loginMessage--success" data-testid="auth-notice-password-reset-success">
              {t('auth.passwordResetSuccess')}
            </p>
          )}
          {errorKey === 'auth.invalidCredentials' && (
            <p className="loginMessage loginMessage--error" data-testid="auth-error-generic">
              {t('auth.invalidCredentials')}
            </p>
          )}
          {errorKey === 'auth.unauthenticated' && (
            <p className="loginMessage loginMessage--error" data-testid="auth-error-session-expired">
              {t('auth.unauthenticated')}
            </p>
          )}
          {errorKey === 'auth.noCommercialProfile' && (
            <p className="loginMessage loginMessage--error" data-testid="auth-error-no-commercial-profile">
              {t('auth.noCommercialProfile')}
            </p>
          )}
          {errorKey === 'auth.noPermission' && (
            <p className="loginMessage loginMessage--error" data-testid="auth-error-no-permission">
              {t('auth.noPermission')}
            </p>
          )}
          {errorKey === 'tenant.invalid' && (
            <p className="loginMessage loginMessage--error" data-testid="auth-error-tenant">
              {t('tenant.invalid')}
            </p>
          )}

          <form className="loginForm" data-testid="login-form" onSubmit={handleSubmit}>
            {nativeApp && (
              <label className="loginField">
                <span className="loginField__label">{t('login.tenant')}</span>
                <TextBox
                  className="loginField__input"
                  value={tenant}
                  stylingMode="outlined"
                  inputAttr={{
                    name: 'tenant',
                    autoComplete: 'organization',
                    placeholder: t('login.tenantPlaceholder'),
                    'data-testid': 'loginTenant',
                  }}
                  onValueChanged={(event) => {
                    setTenant(String(event.value ?? ''));
                  }}
                />
              </label>
            )}
            <label className="loginField">
              <span className="loginField__label">{t('login.username')}</span>
              <TextBox
                className="loginField__input"
                value={codigo}
                stylingMode="outlined"
                inputAttr={{
                  name: 'codigo',
                  autoComplete: 'username',
                  placeholder: t('login.username'),
                  'data-testid': 'loginUsername',
                }}
                onValueChanged={(event) => {
                  setCodigo(String(event.value ?? ''));
                }}
              />
            </label>
            <label className="loginField">
              <span className="loginField__label">{t('login.password')}</span>
              <TextBox
                className="loginField__input"
                value={password}
                mode="password"
                stylingMode="outlined"
                inputAttr={{
                  name: 'password',
                  autoComplete: 'current-password',
                  placeholder: t('login.password'),
                  'data-testid': 'loginPassword',
                }}
                onValueChanged={(event) => {
                  setPassword(String(event.value ?? ''));
                }}
              />
            </label>
            <Button
              className="loginForm__submit"
              type="default"
              useSubmitBehavior
              disabled={isSubmitting}
              text={isSubmitting ? t('login.loading') : t('login.submit')}
              elementAttr={{ 'data-testid': 'login-submit' }}
            />
          </form>

          {!nativeApp && (
            <Link className="loginCard__link" to="/forgot-password" data-testid="login-forgot-password">
              {t('login.forgotPasswordLink')}
            </Link>
          )}
        </div>
      </section>
    </main>
  );
}
