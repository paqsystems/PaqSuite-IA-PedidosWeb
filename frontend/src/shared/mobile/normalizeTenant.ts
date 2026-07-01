export function normalizeTenant(rawValue: string): string {
  return rawValue.trim().toLowerCase();
}

export function isValidTenantSlug(tenant: string): boolean {
  return tenant.length > 0 && /^[a-z0-9_-]+$/.test(tenant);
}
