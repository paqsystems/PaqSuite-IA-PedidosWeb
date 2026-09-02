import { act, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { emptyComprobanteCabecera } from '../types/comprobanteCabecera';
import { leyendaMaxCaracteres } from '../utils/recortarLeyendaCabecera';

const capturedTextBoxes: Array<{
  maxLength?: number;
  inputAttr?: { 'data-testid'?: string };
}> = [];

vi.mock('react-i18next', () => ({
  useTranslation: () => ({ t: (key: string) => key }),
}));

vi.mock('devextreme-react/text-box', () => ({
  default: (props: {
    maxLength?: number;
    inputAttr?: { 'data-testid'?: string };
  }) => {
    capturedTextBoxes.push(props);
    return null;
  },
}));

import { ComprobanteLeyendasPie } from './ComprobanteLeyendasPie';

function renderPie(ui: ReactElement) {
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

describe('ComprobanteLeyendasPie', () => {
  beforeEach(() => {
    capturedTextBoxes.length = 0;
  });

  it('pasa maxLength 60 a los cinco TextBox y conserva data-testid', () => {
    const mounted = renderPie(
      <ComprobanteLeyendasPie
        cabecera={emptyComprobanteCabecera('CLI001')}
        readOnly={false}
        onChange={() => undefined}
      />,
    );

    expect(mounted.container.querySelector('[data-testid="leyendas-pie"]')).not.toBeNull();
    expect(capturedTextBoxes).toHaveLength(5);
    expect(capturedTextBoxes.every((box) => box.maxLength === leyendaMaxCaracteres)).toBe(true);
    expect(capturedTextBoxes.map((box) => box.inputAttr?.['data-testid'])).toEqual([
      'leyenda-1',
      'leyenda-2',
      'leyenda-3',
      'leyenda-4',
      'leyenda-5',
    ]);

    mounted.unmount();
  });
});
