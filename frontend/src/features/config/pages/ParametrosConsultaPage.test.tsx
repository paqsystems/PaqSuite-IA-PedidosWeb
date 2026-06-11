import { act, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { describe, expect, it, vi, beforeEach } from 'vitest';

const capturedColumns: Array<{ dataField?: string; alignment?: string; cssClass?: string }> = [];

vi.mock('devextreme-react/data-grid', () => ({
  Column: (props: { dataField?: string; alignment?: string; cssClass?: string }) => {
    capturedColumns.push(props);
    return null;
  },
}));

vi.mock('../../consultas/components/ConsultaGridPage', () => ({
  ConsultaGridPage: ({ columns }: { columns: ReactElement }) => columns,
}));

vi.mock('../api/parametrosConsultaApi', () => ({
  fetchParametrosConsulta: vi.fn().mockResolvedValue({ items: [] }),
}));

import { ParametrosConsultaPage } from './ParametrosConsultaPage';

function renderPage() {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const root: Root = createRoot(container);

  act(() => {
    root.render(<ParametrosConsultaPage />);
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

describe('ParametrosConsultaPage', () => {
  beforeEach(() => {
    capturedColumns.length = 0;
  });

  it('centra la columna Valor', () => {
    const mounted = renderPage();

    const valorColumn = capturedColumns.find((column) => column.dataField === 'valorMostrado');

    expect(valorColumn?.alignment).toBe('center');
    expect(valorColumn?.cssClass).toBe('parametrosConsultaPage__valorColumn');

    mounted.unmount();
  });
});
