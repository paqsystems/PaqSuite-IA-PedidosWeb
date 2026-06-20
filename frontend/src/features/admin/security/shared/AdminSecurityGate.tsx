import type { ReactElement } from 'react';
import { Navigate } from 'react-router-dom';
import { useAdminSecurityEnabled } from './useAdminSecurityEnabled';

type AdminSecurityGateProps = {
  children: ReactElement;
};

export function AdminSecurityGate({ children }: AdminSecurityGateProps) {
  const { enabled, isLoading } = useAdminSecurityEnabled();

  if (isLoading) {
    return <section data-testid="admin-security-loading" />;
  }

  if (!enabled) {
    return <Navigate to="/dashboard" replace />;
  }

  return children;
}
