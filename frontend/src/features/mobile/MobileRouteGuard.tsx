import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { isNativeApp } from '../../shared/platform/isNativeApp';
import { getMobileDefaultRoute, isRouteAllowedOnMobileApp } from './pedidosWebMobilePolicy';

export function MobileRouteGuard() {
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    if (!isNativeApp()) {
      return;
    }

    if (!isRouteAllowedOnMobileApp(location.pathname)) {
      navigate(getMobileDefaultRoute(), { replace: true });
    }
  }, [location.pathname, navigate]);

  return null;
}
