export const mobileExcludedRoutePrefixes = [
  '/admin',
  '/excel-import',
  '/pivot',
  '/chat-assistant',
  '/demo',
] as const;

export function isRouteAllowedOnMobile(pathname: string): boolean {
  if (!pathname.startsWith('/')) {
    return true;
  }

  return !mobileExcludedRoutePrefixes.some((prefix) => pathname.startsWith(prefix));
}
