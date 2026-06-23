export const chatAssistantSupportEmail = 'ayuda@paqsystems.com.ar';

const defaultProjectName = 'Pedidos Web';

export function resolveChatAssistantProjectName(): string {
  const configured = String(import.meta.env.VITE_CHAT_ASSISTANT_PROJECT_NAME ?? '').trim();
  return configured !== '' ? configured : defaultProjectName;
}
