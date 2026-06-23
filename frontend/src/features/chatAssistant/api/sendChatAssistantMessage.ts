import { apiRequest } from '../../../shared/http/client';
import type { ChatAssistantImagePayload } from '../model/chatAssistantImage';
import type { ChatAssistantReply } from '../model/chatAssistantMessage';

export type SendChatAssistantMessagePayload = {
  message: string;
  credentialId?: number;
  images?: ChatAssistantImagePayload[];
};

export async function sendChatAssistantMessage(
  payload: SendChatAssistantMessagePayload,
): Promise<ChatAssistantReply> {
  const response = await apiRequest<ChatAssistantReply>('/chat-assistant/messages', {
    method: 'POST',
    body: JSON.stringify({
      message: payload.message,
      ...(payload.credentialId ? { credentialId: payload.credentialId } : {}),
      ...(payload.images && payload.images.length > 0 ? { images: payload.images } : {}),
    }),
  });

  return response.resultado;
}
