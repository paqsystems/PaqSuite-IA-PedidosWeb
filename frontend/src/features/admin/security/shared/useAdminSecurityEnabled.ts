import { useEffect, useState } from 'react';
import { fetchPublicConfig } from '../../../config/api/publicConfigApi';

export type AdminSecurityEnabledState = {
  enabled: boolean;
  isLoading: boolean;
  loadError: boolean;
};

export function useAdminSecurityEnabled(): AdminSecurityEnabledState {
  const [enabled, setEnabled] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState(false);

  useEffect(() => {
    let mounted = true;

    void fetchPublicConfig()
      .then((config) => {
        if (mounted) {
          setEnabled(config.securityAdminEnabled);
          setLoadError(false);
        }
      })
      .catch(() => {
        if (mounted) {
          setEnabled(false);
          setLoadError(true);
        }
      })
      .finally(() => {
        if (mounted) {
          setIsLoading(false);
        }
      });

    return () => {
      mounted = false;
    };
  }, []);

  return { enabled, isLoading, loadError };
}
