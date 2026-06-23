import { describe, expect, it } from 'vitest';
import {
  chatAssistantMaxImageBytes,
  chatAssistantMaxImages,
} from '../model/chatAssistantImage';
import { resolveChatAssistantImageValidationErrorKey } from './validateChatAssistantImages';

function createFile(name: string, type: string, size: number): File {
  const content = new Uint8Array(size);
  return new File([content], name, { type });
}

describe('resolveChatAssistantImageValidationErrorKey', () => {
  it('rejects unsupported formats', () => {
    const file = createFile('document.pdf', 'application/pdf', 1024);

    expect(resolveChatAssistantImageValidationErrorKey(file, 0)).toBe(
      'chatAssistant.images.invalidFormat',
    );
  });

  it('rejects files larger than 5 MB', () => {
    const file = createFile('captura.png', 'image/png', chatAssistantMaxImageBytes + 1);

    expect(resolveChatAssistantImageValidationErrorKey(file, 0)).toBe(
      'chatAssistant.images.tooLarge',
    );
  });

  it('rejects more than four images', () => {
    const file = createFile('captura.png', 'image/png', 1024);

    expect(resolveChatAssistantImageValidationErrorKey(file, chatAssistantMaxImages)).toBe(
      'chatAssistant.images.tooMany',
    );
  });

  it('accepts valid png files', () => {
    const file = createFile('captura.png', 'image/png', 1024);

    expect(resolveChatAssistantImageValidationErrorKey(file, 0)).toBeNull();
  });
});
