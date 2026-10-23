import type { ReactElement } from 'react';
import { useTranslation } from 'react-i18next';
import { useAdminSecurityEnabled } from './useAdminSecurityEnabled';

type AdminSecurityGateProps = {
  children: ReactElement;
};

export function AdminSecurityGate({ children }: AdminSecurityGateProps) {
  const { t } = useTranslation();
  const { enabled, isLoading, loadError } = useAdminSecurityEnabled();

  if (isLoading) {
    return <section data-testid="admin-security-loading" />;
  }

  if (loadError) {
    return (
      <section data-testid="admin-security-config-error">
        <h2>{t('admin.security.title')}</h2>
        <p role="alert">{t('admin.security.configError')}</p>
      </section>
    );
  }

  if (!enabled) {
    return (
      <section data-testid="admin-security-disabled">
        <h2>{t('admin.security.title')}</h2>
        <p role="alert">{t('admin.security.disabled')}</p>
      </section>
    );
  }

  return children;
}
