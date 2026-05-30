import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from '../../features/auth/AuthProvider';
import { LoginPage } from '../../features/auth/LoginPage';
import { authenticatedHomePath, protectedRouteElements } from './protectedRoutes';

function LoginRoute() {
  const { isAuthenticated, isBootstrapping, sessionContext, setSessionContext } = useAuth();

  if (isBootstrapping) {
    return (
      <main>
        <p data-testid="auth-bootstrapping">Cargando sesion...</p>
      </main>
    );
  }

  if (isAuthenticated) {
    if (sessionContext?.firstLogin) {
      return <Navigate to="/change-password" replace />;
    }

    return <Navigate to={authenticatedHomePath} replace />;
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
  const { isAuthenticated, isBootstrapping, sessionContext } = useAuth();

  if (isBootstrapping) {
    return (
      <main>
        <p data-testid="auth-bootstrapping">Cargando sesion...</p>
      </main>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (sessionContext?.firstLogin) {
    return <Navigate to="/change-password" replace />;
  }

  return <Navigate to={authenticatedHomePath} replace />;
}

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginRoute />} />
      {protectedRouteElements}
      <Route path="/" element={<RootRedirect />} />
      <Route path="*" element={<RootRedirect />} />
    </Routes>
  );
}
