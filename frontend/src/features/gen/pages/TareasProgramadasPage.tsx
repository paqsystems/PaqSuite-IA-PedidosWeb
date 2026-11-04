import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { TaskProcessesPage } from '@paqsuite/react-core';
import { getStoredToken } from '../../auth/authStorage';
import { getActiveTenantSync } from '../../../shared/mobile/mobileRuntime';

export function TareasProgramadasPage() {
  const { t } = useTranslation();
  const accessToken = getStoredToken() ?? undefined;
  const platform = useMemo(
    () => ({
      cliente: getActiveTenantSync(),
      tenancy: 'single' as const,
    }),
    [],
  );

  return (
    <div data-testid="page-tareas-programadas">
      <TaskProcessesPage t={t} platform={platform} accessToken={accessToken} />
    </div>
  );
}
