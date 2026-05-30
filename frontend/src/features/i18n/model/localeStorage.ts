export const guestLocaleStorageKey = 'pedidosweb.locale.guest';

export function readGuestLocale(): string | null {
  try {
    return localStorage.getItem(guestLocaleStorageKey);
  } catch {
    return null;
  }
}

export function writeGuestLocale(locale: string): void {
  try {
    localStorage.setItem(guestLocaleStorageKey, locale);
  } catch {
    // Sin almacenamiento disponible: el idioma sigue activo en memoria.
  }
}
