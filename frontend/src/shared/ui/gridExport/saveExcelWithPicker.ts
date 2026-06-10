const excelMimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

function triggerSilentDownload(buffer: ArrayBuffer, fileName: string): void {
  const blob = new Blob([buffer], { type: excelMimeType });
  const objectUrl = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = objectUrl;
  anchor.download = fileName;
  anchor.rel = 'noopener';
  anchor.click();
  URL.revokeObjectURL(objectUrl);
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

export async function saveExcelWithPicker(buffer: ArrayBuffer, suggestedName: string): Promise<void> {
  const picker = window.showSaveFilePicker;

  if (shouldUseNativeSavePicker() && typeof picker === 'function') {
    try {
      const handle = await picker({
        suggestedName,
        types: [
          {
            description: 'Excel',
            accept: { [excelMimeType]: ['.xlsx'] },
          },
        ],
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

  triggerSilentDownload(buffer, suggestedName);
}
