import { useEffect, useState } from 'react';
import { fetchPublicConfig } from '../../../config/api/publicConfigApi';

export function useAdminSecurityEnabled(): {
  enabled: boolean;
  isLoading: boolean;
} {
  const [enabled, setEnabled] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let mounted = true;

    void fetchPublicConfig()
      .then((config) => {
        if (mounted) {
          setEnabled(config.securityAdminEnabled);
        }
      })
      .catch(() => {
        if (mounted) {
          setEnabled(false);
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

  return { enabled, isLoading };
}
