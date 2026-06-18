import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import DataGrid, { Column, FilterRow, LoadPanel, Pager, Paging, Scrolling } from 'devextreme-react/data-grid';
import CustomStore from 'devextreme/data/custom_store';
import {
  fetchExcelStagingColumnas,
  fetchExcelStagingFilas,
  flattenStagingRow,
  type ExcelStagingColumn,
} from '../api/excelImportApi';

const defaultPageSize = 50;

type ExcelImportErrorGridProps = {
  guidImportacion: string;
};

function withNumeroFilaColumn(columnas: ExcelStagingColumn[], t: (key: string) => string): ExcelStagingColumn[] {
  return [
    {
      dataField: 'numeroFilaExcel',
      caption: t('excelImport.column.numeroFilaExcel'),
      tipoDato: 'entero',
      format: '#,##0',
    },
    ...columnas,
  ];
}

export function ExcelImportErrorGrid({ guidImportacion }: ExcelImportErrorGridProps) {
  const { t } = useTranslation();
  const [columnas, setColumnas] = useState<ExcelStagingColumn[]>([]);

  useEffect(() => {
    let mounted = true;
    void fetchExcelStagingColumnas(guidImportacion).then((meta) => {
      if (mounted) {
        setColumnas(withNumeroFilaColumn(meta.columnas, t));
      }
    });

    return () => {
      mounted = false;
    };
  }, [guidImportacion, t]);

  const dataSource = useMemo(
    () =>
      new CustomStore({
        key: 'idImportacionFila',
        load: async (loadOptions) => {
          const take = loadOptions.take ?? defaultPageSize;
          const skip = loadOptions.skip ?? 0;
          const page = Math.floor(skip / take) + 1;
          const filas = await fetchExcelStagingFilas(guidImportacion, page, take, true);

          return {
            data: filas.items.map(flattenStagingRow),
            totalCount: filas.total,
          };
        },
      }),
    [guidImportacion],
  );

  return (
    <div className="excelImportHostModal__errorGrid" data-testid="excelHostErrorGrid">
      <DataGrid
        dataSource={dataSource}
        showBorders={true}
        rowAlternationEnabled={true}
        onRowPrepared={(event) => {
          event.rowElement.classList.add('excel-import-row-error');
        }}
      >
        <LoadPanel enabled={true} />
        <Scrolling mode="standard" />
        <Paging defaultPageSize={defaultPageSize} />
        <Pager showInfo={true} showNavigationButtons={true} />
        <FilterRow visible={true} />
        {columnas.map((columna) => (
          <Column
            key={columna.dataField}
            dataField={columna.dataField}
            caption={
              columna.dataField === 'errorImportacion'
                ? t('excelImport.column.errores')
                : columna.caption
            }
            dataType={
              columna.tipoDato === 'fecha'
                ? 'date'
                : columna.tipoDato === 'decimal' || columna.tipoDato === 'entero'
                  ? 'number'
                  : undefined
            }
            format={columna.format ?? undefined}
          />
        ))}
      </DataGrid>
    </div>
  );
}
