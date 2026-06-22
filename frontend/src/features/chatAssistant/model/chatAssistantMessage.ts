export type ChatAssistantMessageRole = 'user' | 'assistant';

export type ChatAssistantMessage = {
  id: string;
  role: ChatAssistantMessageRole;
  content: string;
  references?: ChatAssistantDocumentReference[];
  requiresSupportFollowup?: boolean;
};

export type ChatAssistantDocumentReference = {
  title: string;
  path: string;
};

export type ChatAssistantReply = {
  reply: string;
  references: ChatAssistantDocumentReference[];
  requiresSupportFollowup: boolean;
};

export const chatAssistantTextOnlyMaxLength = 2000;

export const chatAssistantTextWithImagesMaxLength = 1000;
