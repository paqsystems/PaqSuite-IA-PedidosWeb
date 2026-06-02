import { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Popup from 'devextreme-react/popup';
import { Column } from 'devextreme-react/data-grid';
import { useGridLayouts } from '../../gridLayouts/hooks/useGridLayouts';
import { DataGridDx, type DataGridDxHandle, type DataGridRowAction } from '../../../shared/ui/grids';
import {
  fetchHistorialVentas,
  fetchHistorialVentasDetalle,
  type HistorialVentaDetalleRow,
  type HistorialVentasRow,
} from '../api/consultaApi';

const proceso = 'pw_historialventas';
const gridId = 'pw_historialventas';

export function HistorialVentasPage() {
  const { t } = useTranslation();
  const gridRef = useRef<DataGridDxHandle>(null);
  const { toolbar: layoutToolbar, saveAsDialog } = useGridLayouts({
    proceso,
    gridId,
    gridRef,
  });
  const [rows, setRows] = useState<HistorialVentasRow[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [detalleVisible, setDetalleVisible] = useState(false);
  const [detalleRows, setDetalleRows] = useState<HistorialVentaDetalleRow[]>([]);

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      setLoadError(null);
      try {
        const result = await fetchHistorialVentas();
        if (mounted) {
          setRows(result.items);
        }
      } catch {
        if (mounted) {
          setRows([]);
          setLoadError(t('grid.error.load'));
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [t]);

  const handleOpenDetalle = useCallback(async (row: HistorialVentasRow) => {
    setDetalleVisible(true);
    try {
      const detail = await fetchHistorialVentasDetalle(row.id);
      setDetalleRows(detail);
    } catch {
      setDetalleRows([]);
    }
  }, []);

  const rowActions: DataGridRowAction<HistorialVentasRow>[] = [
    {
      actionKey: 'verDetalle',
      icon: 'info',
      hintKey: 'grid.action.viewDetail',
      onClick: (row) => {
        void handleOpenDetalle(row);
      },
    },
  ];

  return (
    <section data-testid="page-consulta-historial">
      <h2>{t('pages.consultaHistorial')}</h2>
      <DataGridDx<HistorialVentasRow>
        ref={gridRef}
        proceso={proceso}
        gridId={gridId}
        dataSource={rows}
        keyExpr="id"
        isLoading={isLoading}
        loadError={loadError}
        toolbarEnd={layoutToolbar}
        rowActions={rowActions}
      >
        <Column dataField="fecha" caption={t('consultas.column.fecha')} />
        <Column dataField="cliente" caption={t('consultas.column.cliente')} />
        <Column dataField="comprobante" caption={t('consultas.column.comprobante')} />
        <Column dataField="importe" caption={t('consultas.column.importe')} dataType="number" format="currency" />
      </DataGridDx>
      {saveAsDialog}
      <Popup
        visible={detalleVisible}
        onHiding={() => setDetalleVisible(false)}
        dragEnabled={false}
        showCloseButton={true}
        width={700}
        height={420}
        title={t('consultas.historialDetalleTitle')}
        elementAttr={{ 'data-testid': 'consultaHistorialDetallePopup' }}
      >
        <DataGridDx<HistorialVentaDetalleRow>
          proceso={proceso}
          gridId="pw_historialventas_detalle"
          dataSource={detalleRows}
          keyExpr="id"
          exportEnabled={false}
          enableGrouping={false}
        >
          <Column dataField="articulo" caption={t('consultas.column.articulo')} />
          <Column dataField="cantidad" caption={t('consultas.column.cantidad')} dataType="number" />
          <Column dataField="importe" caption={t('consultas.column.importe')} dataType="number" format="currency" />
        </DataGridDx>
      </Popup>
    </section>
  );
}
