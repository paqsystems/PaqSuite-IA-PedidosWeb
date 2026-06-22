import {
  chatAssistantAllowedImageExtensions,
  chatAssistantAllowedImageMimeTypes,
  chatAssistantMaxImageBytes,
  chatAssistantMaxImages,
  type ChatAssistantImagePayload,
} from '../model/chatAssistantImage';

export type ChatAssistantImageValidationErrorKey =
  | 'chatAssistant.images.invalidFormat'
  | 'chatAssistant.images.tooLarge'
  | 'chatAssistant.images.tooMany';

export function resolveChatAssistantImageValidationErrorKey(
  file: File,
  currentCount: number,
): ChatAssistantImageValidationErrorKey | null {
  if (currentCount >= chatAssistantMaxImages) {
    return 'chatAssistant.images.tooMany';
  }

  if (file.size > chatAssistantMaxImageBytes) {
    return 'chatAssistant.images.tooLarge';
  }

  const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

  if (
    !chatAssistantAllowedImageMimeTypes.includes(
      file.type as (typeof chatAssistantAllowedImageMimeTypes)[number],
    )
    && !chatAssistantAllowedImageExtensions.includes(
      extension as (typeof chatAssistantAllowedImageExtensions)[number],
    )
  ) {
    return 'chatAssistant.images.invalidFormat';
  }

  return null;
}

export async function encodeChatAssistantImageFile(
  file: File,
): Promise<ChatAssistantImagePayload> {
  const contentBase64 = await readFileAsBase64(file);
  const mimeType = resolveImageMimeType(file);

  return {
    fileName: file.name,
    mimeType,
    contentBase64,
  };
}

function resolveImageMimeType(file: File): string {
  if (file.type && chatAssistantAllowedImageMimeTypes.includes(
    file.type as (typeof chatAssistantAllowedImageMimeTypes)[number],
  )) {
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
