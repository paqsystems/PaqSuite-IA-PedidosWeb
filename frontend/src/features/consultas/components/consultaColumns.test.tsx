import { act, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { describe, expect, it, vi } from 'vitest';
import { ComprobanteConsultaColumns } from './ComprobanteConsultaColumns';
import { DetallePedidosConsultaColumns } from './DetallePedidosConsultaColumns';

const capturedFields: string[] = [];

vi.mock('devextreme-react/data-grid', () => ({
  Column: (props: { dataField?: string }) => {
    if (props.dataField) {
      capturedFields.push(props.dataField);
    }

    return null;
  },
}));

const t = ((key: string) => key) as Parameters<typeof ComprobanteConsultaColumns>[0]['t'];

function renderColumns(ui: ReactElement) {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const root: Root = createRoot(container);

  act(() => {
    root.render(ui);
  });

  return {
    unmount: () => {
      act(() => {
        root.unmount();
      });
      container.remove();
    },
  };
}

describe('consulta columns CC', () => {
  it('ComprobanteConsultaColumns expone nombreFantasia', () => {
    capturedFields.length = 0;
    const mounted = renderColumns(<ComprobanteConsultaColumns t={t} />);

    expect(capturedFields).toContain('nombreFantasia');
    mounted.unmount();
  });

  it('DetallePedidosConsultaColumns expone precioNeto', () => {
    capturedFields.length = 0;
    const mounted = renderColumns(<DetallePedidosConsultaColumns t={t} />);

    expect(capturedFields).toContain('precioNeto');
    mounted.unmount();
  });
});
