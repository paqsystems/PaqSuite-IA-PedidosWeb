export type ChatAssistantImagePayload = {
  fileName: string;
  mimeType: string;
  contentBase64: string;
};

export type ChatAssistantSelectedImage = {
  id: string;
  file: File;
  previewUrl: string;
};

export const chatAssistantMaxImages = 4;

export const chatAssistantMaxImageBytes = 5 * 1024 * 1024;

export const chatAssistantAllowedImageMimeTypes = [
  'image/png',
  'image/jpeg',
  'image/webp',
] as const;

export const chatAssistantAllowedImageExtensions = ['png', 'jpg', 'jpeg', 'webp'] as const;
