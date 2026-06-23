export type ChatAssistantMessageRole = 'user' | 'assistant';

export type ChatAssistantMessage = {
  id: string;
  role: ChatAssistantMessageRole;
  content: string;
  requiresSupportFollowup?: boolean;
};

export type ChatAssistantReply = {
  reply: string;
  requiresSupportFollowup: boolean;
};

export const chatAssistantTextOnlyMaxLength = 2000;

export const chatAssistantTextWithImagesMaxLength = 1000;
