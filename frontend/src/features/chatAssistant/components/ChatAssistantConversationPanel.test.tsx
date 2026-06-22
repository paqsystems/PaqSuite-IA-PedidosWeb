import { act, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { describe, expect, it } from 'vitest';
import { ChatAssistantConversationPanel } from './ChatAssistantConversationPanel';

function renderPanel(ui: ReactElement) {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const root: Root = createRoot(container);

  act(() => {
    root.render(ui);
  });

  return {
    container,
    unmount: () => {
      act(() => {
        root.unmount();
      });
      container.remove();
    },
  };
}

describe('ChatAssistantConversationPanel', () => {
  it('shows the initial message when there are no conversation messages', () => {
    const view = renderPanel(
      <ChatAssistantConversationPanel
        messages={[]}
        lastReply={null}
        initialFallbackText="Fallback inicial"
        supportFollowupFallbackText="Fallback soporte"
      />,
    );

    expect(view.container.querySelector('[data-testid="chatAssistantInitialMessage"]')).not.toBeNull();
    expect(view.container.textContent).toContain('Pedidos Web');
    view.unmount();
  });

  it('shows document references on assistant responses', () => {
    const view = renderPanel(
      <ChatAssistantConversationPanel
        messages={[
          { id: '1', role: 'user', content: '¿Cómo grabo un pedido?' },
          {
            id: '2',
            role: 'assistant',
            content: 'Respuesta orientativa',
            references: [
              { title: 'Manual de carga', path: 'docs/99-manual-usuario/carga.md' },
            ],
          },
        ]}
        lastReply={null}
        initialFallbackText="Fallback inicial"
        supportFollowupFallbackText="Fallback soporte"
      />,
    );

    expect(view.container.querySelector('[data-testid="chatAssistantResponse"]')).not.toBeNull();
    expect(view.container.querySelector('[data-testid="chatAssistantReferences"]')).not.toBeNull();
    expect(view.container.textContent).toContain('Manual de carga');
    view.unmount();
  });

  it('shows support followup only when requiresSupportFollowup is true', () => {
    const withFollowup = renderPanel(
      <ChatAssistantConversationPanel
        messages={[{ id: '1', role: 'assistant', content: 'Respuesta orientativa' }]}
        lastReply={{
          reply: 'Respuesta orientativa',
          references: [],
          requiresSupportFollowup: true,
        }}
        initialFallbackText="Fallback inicial"
        supportFollowupFallbackText="Fallback soporte"
      />,
    );

    expect(
      withFollowup.container.querySelector('[data-testid="chatAssistantSupportFollowup"]'),
    ).not.toBeNull();
    expect(
      withFollowup.container.querySelector('[data-testid="chatAssistantSupportFollowupHidden"]'),
    ).toBeNull();
    withFollowup.unmount();

    const withoutFollowup = renderPanel(
      <ChatAssistantConversationPanel
        messages={[{ id: '1', role: 'assistant', content: 'Respuesta orientativa' }]}
        lastReply={{
          reply: 'Respuesta orientativa',
          references: [],
          requiresSupportFollowup: false,
        }}
        initialFallbackText="Fallback inicial"
        supportFollowupFallbackText="Fallback soporte"
      />,
    );

    expect(
      withoutFollowup.container.querySelector('[data-testid="chatAssistantSupportFollowup"]'),
    ).toBeNull();
    expect(
      withoutFollowup.container.querySelector('[data-testid="chatAssistantSupportFollowupHidden"]'),
    ).not.toBeNull();
    withoutFollowup.unmount();
  });
});
