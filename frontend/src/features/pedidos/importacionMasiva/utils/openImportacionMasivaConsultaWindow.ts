/**
 * Abre la consulta de un borrador en solapa nueva.
 * No usar feature "noopener" en window.open: en Chromium el retorno es null
 * aunque la solapa sí se abra.
 *
 * @returns true si se abrió solapa nueva; false si el popup fue bloqueado.
 */
export function openImportacionMasivaConsultaWindow(consultUrl: string): boolean {
  const newWindow = window.open(consultUrl, '_blank');
  if (!newWindow) {
    return false;
  }

  newWindow.opener = null;
  return true;
}
