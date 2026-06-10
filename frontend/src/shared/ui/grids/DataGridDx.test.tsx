import { act, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { I18nextProvider } from 'react-i18next';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import i18n from '../../../features/i18n/i18n';
import { DataGridDx } from './DataGridDx';
import { Column } from 'devextreme-react/data-grid';
import { getDataGridDxContainerTestId, getDataGridDxToolbarTestId } from './dataGridDxTestIds';

vi.mock('../gridExport', () => ({
  GridExportButton: () => null,
}));

vi.mock('devextreme-react/data-grid', async () => {
  const React = await import('react');

  const passthrough = (tag: string) =>
    function MockComponent({ children }: { children?: React.ReactNode }) {
      return React.createElement(tag, null, children);
    };

  const DataGrid = React.forwardRef(function MockDataGrid(
    props: {
      children?: React.ReactNode;
      onContentReady?: (event: { component: unknown }) => void;
    },
    ref: React.Ref<unknown>,
  ) {
    const gridInstance = {
      getVisibleRows: () => [{ rowType: 'data' }, { rowType: 'data' }],
      getVisibleColumns: () => [{ dataField: 'id', type: 'data' }],
      option: vi.fn(),
      state: () => ({}),
    };

    React.useImperativeHandle(ref, () => ({
      instance: () => gridInstance,
    }));

    React.useEffect(() => {
      props.onContentReady?.({ component: gridInstance });
    }, [props.onContentReady]);

    return React.createElement('div', { 'data-testid': 'mock-data-grid' }, props.children);
  });

  return {
    default: DataGrid,
    Button: passthrough('span'),
    Column: passthrough('div'),
    ColumnChooser: passthrough('div'),
    Editing: passthrough('div'),
    FilterRow: passthrough('div'),
    GroupPanel: passthrough('div'),
    LoadPanel: passthrough('div'),
    Pager: passthrough('div'),
    Paging: passthrough('div'),
    Sorting: passthrough('div'),
    Summary: passthrough('div'),
    Toolbar: passthrough('div'),
    Item: passthrough('div'),
  };
});

type DemoRow = { id: number; name: string };

const demoData: DemoRow[] = [
  { id: 1, name: 'Alpha' },
  { id: 2, name: 'Beta' },
];

function renderDataGridDx(ui: ReactElement) {
  const container = document.createElement('div');
  document.body.appendChild(container);
  const root: Root = createRoot(container);

  act(() => {
    root.render(<I18nextProvider i18n={i18n}>{ui}</I18nextProvider>);
  });

  return {
    container,
    root,
    unmount: () => {
      act(() => {
        root.unmount();
      });
      container.remove();
    },
  };
}

describe('DataGridDx', () => {
  let mounted: ReturnType<typeof renderDataGridDx> | null = null;

  beforeEach(() => {
    mounted = null;
  });

  afterEach(() => {
    mounted?.unmount();
    mounted = null;
  });

  it('renderiza contenedor y grilla mock con datos', () => {
    mounted = renderDataGridDx(
      <DataGridDx<DemoRow>
        proceso="pw_test"
        gridId="main"
        dataSource={demoData}
        keyExpr="id"
        exportEnabled={false}
      >
        <Column dataField="id" />
        <Column dataField="name" />
      </DataGridDx>,
    );

    expect(
      mounted.container.querySelector(`[data-testid="${getDataGridDxContainerTestId('main')}"]`),
    ).not.toBeNull();
    expect(mounted.container.querySelector('[data-testid="mock-data-grid"]')).not.toBeNull();
  });

  it('muestra mensaje de carga', () => {
    mounted = renderDataGridDx(
      <DataGridDx proceso="pw_test" gridId="loading" dataSource={[]} isLoading />,
    );

    expect(mounted.container.querySelector('[data-testid="dataGridDxLoading"]')).not.toBeNull();
  });

  it('muestra mensaje de error', () => {
    mounted = renderDataGridDx(
      <DataGridDx proceso="pw_test" gridId="error" dataSource={[]} loadError="fallo" />,
    );

    expect(mounted.container.querySelector('[data-testid="dataGridDxError"]')).not.toBeNull();
  });

  it('expone toolbar cuando hay exportacion activa', () => {
    mounted = renderDataGridDx(
      <DataGridDx proceso="pw_test" gridId="toolbar" dataSource={demoData} exportEnabled />,
    );

    expect(
      mounted.container.querySelector(`[data-testid="${getDataGridDxToolbarTestId('toolbar')}"]`),
    ).not.toBeNull();
  });
});
