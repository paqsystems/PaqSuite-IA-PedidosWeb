const excelMimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

function ensureXlsxFileName(fileName: string): string {
  const trimmed = fileName.trim() || 'export.xlsx';
  return trimmed.toLowerCase().endsWith('.xlsx') ? trimmed : `${trimmed}.xlsx`;
}

function triggerSilentDownload(buffer: ArrayBuffer, fileName: string): void {
  const safeName = ensureXlsxFileName(fileName);
  const blob = new Blob([buffer], { type: excelMimeType });
  const objectUrl = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = objectUrl;
  anchor.download = safeName;
  anchor.rel = 'noopener';
  anchor.style.display = 'none';
  document.body.appendChild(anchor);
  anchor.click();
  document.body.removeChild(anchor);
  // Firefox puede truncar la descarga si se revoca el URL de inmediato.
  window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1500);
}

/**
 * Guarda el workbook: diálogo del sistema si hay `showSaveFilePicker`; si no, descarga silenciosa (R-C1-01).
 */
function shouldUseNativeSavePicker(): boolean {
  if (typeof window.showSaveFilePicker !== 'function') {
    return false;
  }

  // Playwright/Automation: el picker nativo no completa; usar descarga silenciosa (E2E).
  if (navigator.webdriver) {
    return false;
  }

  return true;
}

const excelSavePickerTypes = [
  {
    description: 'Excel',
    accept: { [excelMimeType]: ['.xlsx'] },
  },
];

export async function saveExcelWithPicker(buffer: ArrayBuffer, suggestedName: string): Promise<void> {
  const safeName = ensureXlsxFileName(suggestedName);
  const picker = window.showSaveFilePicker;

  if (shouldUseNativeSavePicker() && typeof picker === 'function') {
    try {
      const handle = await picker({
        suggestedName: safeName,
        types: excelSavePickerTypes,
      });
      const writable = await handle.createWritable();
      await writable.write(buffer);
      await writable.close();
      return;
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') {
        return;
      }
    }
  }

  triggerSilentDownload(buffer, safeName);
}

/**
 * Abre el diálogo "Guardar como" antes de cargar el buffer.
 * Así se conserva la activación de usuario (si se hace fetch primero, el picker falla y Firefox
 * descarga el blob con un nombre UUID).
 */
export async function saveExcelWithPickerLazy(
  suggestedName: string,
  loadBuffer: () => Promise<ArrayBuffer>,
): Promise<void> {
  const safeName = ensureXlsxFileName(suggestedName);
  const picker = window.showSaveFilePicker;

  if (shouldUseNativeSavePicker() && typeof picker === 'function') {
    try {
      const handle = await picker({
        suggestedName: safeName,
        types: excelSavePickerTypes,
      });
      const buffer = await loadBuffer();
      const writable = await handle.createWritable();
      await writable.write(buffer);
      await writable.close();
      return;
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') {
        return;
      }
    }
  }

  const buffer = await loadBuffer();
  triggerSilentDownload(buffer, safeName);
}
