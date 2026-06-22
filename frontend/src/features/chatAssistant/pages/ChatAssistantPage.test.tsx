import { act, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { describe, expect, it, vi } from 'vitest';
import { ChatAssistantPageView } from '../pages/ChatAssistantPage';

vi.mock('devextreme-react/button', () => ({
  default: ({
    text,
    onClick,
    elementAttr,
  }: {
    text: string;
    onClick?: () => void;
    elementAttr?: { 'data-testid'?: string };
  }) => (
    <button type="button" data-testid={elementAttr?.['data-testid']} onClick={onClick}>
      {text}
    </button>
  ),
}));

function renderView(ui: ReactElement) {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const root: Root = createRoot(container);

  act(() => {
    root.render(<MemoryRouter>{ui}</MemoryRouter>);
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

describe('ChatAssistantPageView', () => {
  it('shows empty state when chat is not operational', () => {
    const view = renderView(<ChatAssistantPageView isOperational={false} />);

    expect(view.container.querySelector('[data-testid="chatAssistantEmptyState"]')).not.toBeNull();
    expect(
      view.container.querySelector('[data-testid="chatAssistantEmptyStateConfigurationCta"]'),
    ).not.toBeNull();
    view.unmount();
  });
});
