import type { CargaAsistenteImagePayload } from '../model/cargaAsistenteTypes';

export const cargaAsistenteMaxImages = 4;

export const cargaAsistenteAllowedImageMimeTypes = [
  'image/png',
  'image/jpeg',
  'image/webp',
] as const;

export const cargaAsistenteAllowedImageExtensions = ['png', 'jpg', 'jpeg', 'webp'] as const;

export function isAllowedCargaAsistenteImageFile(file: File): boolean {
  const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

  if (
    cargaAsistenteAllowedImageMimeTypes.includes(
      file.type as (typeof cargaAsistenteAllowedImageMimeTypes)[number],
    )
  ) {
    return true;
  }

  return cargaAsistenteAllowedImageExtensions.includes(
    extension as (typeof cargaAsistenteAllowedImageExtensions)[number],
  );
}

export async function fileToBase64Image(file: File): Promise<CargaAsistenteImagePayload> {
  const contentBase64 = await readFileAsBase64(file);

  return {
    fileName: file.name,
    mimeType: resolveImageMimeType(file),
    contentBase64,
  };
}

function resolveImageMimeType(file: File): string {
  if (
    file.type &&
    cargaAsistenteAllowedImageMimeTypes.includes(
      file.type as (typeof cargaAsistenteAllowedImageMimeTypes)[number],
    )
  ) {
    return file.type;
  }

  const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

  if (extension === 'png') {
    return 'image/png';
  }

  if (extension === 'webp') {
    return 'image/webp';
  }

  return 'image/jpeg';
}

function readFileAsBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = () => {
      const result = String(reader.result ?? '');
      const commaIndex = result.indexOf(',');

      resolve(commaIndex >= 0 ? result.slice(commaIndex + 1) : result);
    };

    reader.onerror = () => {
      reject(reader.error ?? new Error('Failed to read image file.'));
    };

    reader.readAsDataURL(file);
  });
}
