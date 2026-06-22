import {
  chatAssistantSupportEmail,
  resolveChatAssistantProjectName,
} from '../config/chatAssistantContentConfig';

export type ChatAssistantMessagePlaceholders = {
  proyecto: string;
  supportEmail: string;
};

export function resolveChatAssistantMessagePlaceholders(): ChatAssistantMessagePlaceholders {
  return {
    proyecto: resolveChatAssistantProjectName(),
    supportEmail: chatAssistantSupportEmail,
  };
}

export function replaceMessagePlaceholders(
  content: string,
  placeholders: ChatAssistantMessagePlaceholders = resolveChatAssistantMessagePlaceholders(),
): string {
  return content
    .replace(/\{\{Proyecto\}\}/g, placeholders.proyecto)
    .replace(/\{\{supportEmail\}\}/g, placeholders.supportEmail);
}
