import type { CargaAsistenteImagePayload } from '../model/cargaAsistenteTypes';

export const cargaAsistenteMaxImages = 4;

export const cargaAsistenteMaxImageBytes = 5 * 1024 * 1024;

/** Tope de lado largo al redimensionar adjuntos (reduce payload / timeouts vision). */
export const cargaAsistenteImageMaxEdgePx = 1600;

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
  if (file.size > cargaAsistenteMaxImageBytes) {
    throw new Error('chatAssistant.images.tooLarge');
  }

  const optimized = await optimizeImageForUpload(file);

  return {
    fileName: optimized.fileName,
    mimeType: optimized.mimeType,
    contentBase64: optimized.contentBase64,
  };
}

type OptimizedImage = {
  fileName: string;
  mimeType: string;
  contentBase64: string;
};

async function optimizeImageForUpload(file: File): Promise<OptimizedImage> {
  const originalMime = resolveImageMimeType(file);
  const dataUrl = await readFileAsDataUrl(file);

  try {
    const image = await loadHtmlImage(dataUrl);
    const longestEdge = Math.max(image.naturalWidth, image.naturalHeight);
    const needsResize = longestEdge > cargaAsistenteImageMaxEdgePx;
    const needsReencode = file.size > 900 * 1024 || needsResize;

    if (!needsReencode) {
      return {
        fileName: file.name,
        mimeType: originalMime,
        contentBase64: stripDataUrlBase64(dataUrl),
      };
    }

    const scale = needsResize ? cargaAsistenteImageMaxEdgePx / longestEdge : 1;
    const width = Math.max(1, Math.round(image.naturalWidth * scale));
    const height = Math.max(1, Math.round(image.naturalHeight * scale));
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');

    if (!context) {
      return {
        fileName: file.name,
        mimeType: originalMime,
        contentBase64: stripDataUrlBase64(dataUrl),
      };
    }

    context.drawImage(image, 0, 0, width, height);
    const jpegDataUrl = canvas.toDataURL('image/jpeg', 0.82);
    const baseName = file.name.replace(/\.[^.]+$/, '') || 'adjunto';

    return {
      fileName: `${baseName}.jpg`,
      mimeType: 'image/jpeg',
      contentBase64: stripDataUrlBase64(jpegDataUrl),
    };
  } catch {
    return {
      fileName: file.name,
      mimeType: originalMime,
      contentBase64: stripDataUrlBase64(dataUrl),
    };
  }
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

function stripDataUrlBase64(dataUrl: string): string {
  const commaIndex = dataUrl.indexOf(',');
  return commaIndex >= 0 ? dataUrl.slice(commaIndex + 1) : dataUrl;
}

function readFileAsDataUrl(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = () => {
      resolve(String(reader.result ?? ''));
    };

    reader.onerror = () => {
      reject(reader.error ?? new Error('Failed to read image file.'));
    };

    reader.readAsDataURL(file);
  });
}

function loadHtmlImage(dataUrl: string): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const image = new Image();
    image.onload = () => resolve(image);
    image.onerror = () => reject(new Error('Failed to decode image.'));
    image.src = dataUrl;
  });
}
