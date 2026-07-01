import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { isNativeApp } from '../../shared/platform/isNativeApp';
import { isRouteAllowedOnMobile } from './mobileMenuPolicy';
import { getMobileDefaultRoute } from './pedidosWebMobilePolicy';

export function MobileRouteGuard() {
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    if (!isNativeApp()) {
      return;
    }

    if (!isRouteAllowedOnMobile(location.pathname)) {
      navigate(getMobileDefaultRoute(), { replace: true });
    }
  }, [location.pathname, navigate]);

  return null;
}
