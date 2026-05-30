import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../../features/auth/AuthProvider';

export function RequirePasswordChange() {
  const { sessionContext } = useAuth();

  if (sessionContext?.firstLogin) {
    return <Navigate to="/change-password" replace />;
  }

  return <Outlet />;
}
