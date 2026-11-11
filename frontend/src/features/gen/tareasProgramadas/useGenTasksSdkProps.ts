import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { getStoredToken } from '../../auth/authStorage';
import { getActiveTenantSync } from '../../../shared/mobile/mobileRuntime';

export function useGenTasksSdkProps() {
  const { t } = useTranslation();
  const accessToken = getStoredToken() ?? undefined;
  const platform = useMemo(
    () => ({
      cliente: getActiveTenantSync(),
      tenancy: 'single' as const,
    }),
    [],
  );

  return { t, accessToken, platform };
}
