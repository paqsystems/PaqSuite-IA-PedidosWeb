import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { EmissionReportDesignerPage, isNativeApp } from '@paqsuite/react-core';
import { useRequiredSessionContext } from '../../auth/AuthProvider';
import { getStoredToken } from '../../auth/authStorage';
import { getActiveTenantSync } from '../../../shared/mobile/mobileRuntime';

export function DiseniadorReportesPage() {
  const sessionContext = useRequiredSessionContext();
  const { t } = useTranslation();
  const native = isNativeApp();
  const accessToken = getStoredToken() ?? undefined;
  const platform = useMemo(
    () => ({
      cliente: getActiveTenantSync(),
      tenancy: 'single' as const,
    }),
    [],
  );
  const hasDesignPermission =
    sessionContext.security.accesoTotal ||
    sessionContext.security.roles.includes('Supervisor');

  return (
    <div data-testid="page-diseniador-reportes">
      <EmissionReportDesignerPage
        isNative={native}
        hasDesignPermission={hasDesignPermission}
        hasNotificationServiceAdmin={false}
        accessToken={accessToken}
        platform={platform}
        t={t}
      />
    </div>
  );
}
