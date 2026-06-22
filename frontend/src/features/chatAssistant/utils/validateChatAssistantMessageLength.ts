import {
  chatAssistantTextOnlyMaxLength,
  chatAssistantTextWithImagesMaxLength,
} from '../model/chatAssistantMessage';

export function resolveChatAssistantMessageMaxLength(hasImages: boolean): number {
  return hasImages ? chatAssistantTextWithImagesMaxLength : chatAssistantTextOnlyMaxLength;
}

export function isChatAssistantMessageLengthValid(messageLength: number, hasImages = false): boolean {
  return messageLength > 0 && messageLength <= resolveChatAssistantMessageMaxLength(hasImages);
}

export function isChatAssistantMessageLengthExceeded(messageLength: number, hasImages = false): boolean {
  return messageLength > resolveChatAssistantMessageMaxLength(hasImages);
}
