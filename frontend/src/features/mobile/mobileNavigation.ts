import { isNativeApp } from '../../shared/platform/isNativeApp';
import { getMobileDefaultRoute } from './pedidosWebMobilePolicy';

export function getAuthenticatedHomePath(): string {
  if (isNativeApp()) {
    return getMobileDefaultRoute();
  }

  return '/dashboard';
}
