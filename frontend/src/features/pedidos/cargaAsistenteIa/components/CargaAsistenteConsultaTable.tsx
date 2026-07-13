import { useTranslation } from 'react-i18next';
import {
  buildShowConsultaViewModel,
  numericConsultaColumns,
  type ShowConsultaPayload,
} from '../utils/formatShowConsulta';

export type CargaAsistenteConsultaTableProps = {
  payload: ShowConsultaPayload;
};

export function CargaAsistenteConsultaTable({ payload }: CargaAsistenteConsultaTableProps) {
  const { t } = useTranslation();
  const model = buildShowConsultaViewModel(payload, t);

  if (!model) {
    return null;
  }

  return (
    <div className="cargaAsistenteIaPanel__consultaWrap">
      <table className="cargaAsistenteIaPanel__consultaTable" data-testid="cargaAsistenteIaConsultaTable">
        <thead>
          <tr>
            {model.headers.map((header, index) => {
              const column = model.columns[index] ?? '';
              const isNumeric = numericConsultaColumns.has(column);
              return (
                <th
                  key={`${column}-${header}`}
                  className={isNumeric ? 'cargaAsistenteIaPanel__consultaCell--numeric' : undefined}
                >
                  {header}
                </th>
              );
            })}
          </tr>
        </thead>
        <tbody>
          {model.rows.map((row, rowIndex) => (
            <tr key={`consulta-row-${rowIndex}`}>
              {row.map((cell, cellIndex) => {
                const column = model.columns[cellIndex] ?? '';
                const isNumeric = numericConsultaColumns.has(column);
                return (
                  <td
                    key={`${column}-${rowIndex}-${cellIndex}`}
                    className={isNumeric ? 'cargaAsistenteIaPanel__consultaCell--numeric' : undefined}
                  >
                    {cell}
                  </td>
                );
              })}
            </tr>
          ))}
        </tbody>
        {model.totalsParts.length > 0 ? (
          <tfoot>
            <tr>
              {model.columns.map((column, index) => {
                const isNumeric = numericConsultaColumns.has(column);
                const totalPart = model.totalsParts.find((part) => part.column === column);
                const isFirst = index === 0;
                let content = '';
                if (totalPart) {
                  content = totalPart.value;
                } else if (isFirst) {
                  content = t('pedidos.carga.asistente.consulta.totales');
                }
                return (
                  <td
                    key={`total-${column}`}
                    className={
                      isNumeric ? 'cargaAsistenteIaPanel__consultaCell--numeric' : undefined
                    }
                  >
                    {content}
                  </td>
                );
              })}
            </tr>
          </tfoot>
        ) : null}
      </table>
      {model.truncatedNote ? (
        <p className="cargaAsistenteIaPanel__consultaNote">{model.truncatedNote}</p>
      ) : null}
    </div>
  );
}
