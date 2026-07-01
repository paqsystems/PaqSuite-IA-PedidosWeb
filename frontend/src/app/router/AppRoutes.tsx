import { Navigate, Route, Routes } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../features/auth/AuthProvider';
import { ForgotPasswordPage } from '../../features/auth/ForgotPasswordPage';
import { LoginPage } from '../../features/auth/LoginPage';
import { ResetPasswordPage } from '../../features/auth/ResetPasswordPage';
import { protectedRouteElements } from './protectedRoutes';
import { getAuthenticatedHomePath } from '../../features/mobile/mobileNavigation';
function LoginRoute() {
  const { t } = useTranslation();
  const { isAuthenticated, isBootstrapping, sessionContext, setSessionContext } = useAuth();
  const homePath = getAuthenticatedHomePath();

  if (isBootstrapping) {
    return (
      <main>
        <p data-testid="auth-bootstrapping">{t('auth.bootstrapping')}</p>
      </main>
    );
  }

  if (isAuthenticated) {
    if (sessionContext?.firstLogin) {
      return <Navigate to="/change-password" replace />;
    }

    return <Navigate to={homePath} replace />;
  }

  return (
    <LoginPage
      onLoginSuccess={(nextSessionContext) => {
        setSessionContext(nextSessionContext);
      }}
    />
  );
}

function RootRedirect() {
  const { t } = useTranslation();
  const { isAuthenticated, isBootstrapping, sessionContext } = useAuth();
  const homePath = getAuthenticatedHomePath();

  if (isBootstrapping) {
    return (
      <main>
        <p data-testid="auth-bootstrapping">{t('auth.bootstrapping')}</p>
      </main>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (sessionContext?.firstLogin) {
    return <Navigate to="/change-password" replace />;
  }

  return <Navigate to={homePath} replace />;
}

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginRoute />} />
      <Route path="/forgot-password" element={<ForgotPasswordPage />} />
      <Route path="/reset-password" element={<ResetPasswordPage />} />
      {protectedRouteElements}
      <Route path="/" element={<RootRedirect />} />
      <Route path="*" element={<RootRedirect />} />
    </Routes>
  );
}
