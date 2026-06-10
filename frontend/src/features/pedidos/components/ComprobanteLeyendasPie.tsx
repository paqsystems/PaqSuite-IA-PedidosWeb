import { useTranslation } from 'react-i18next';
import TextBox from 'devextreme-react/text-box';
import { isDevExtremeUserChange } from '../../../shared/ui/devextremeUserChange';
import type { ComprobanteCabecera } from '../types/comprobanteCabecera';

type ComprobanteLeyendasPieProps = {
  cabecera: ComprobanteCabecera;
  readOnly: boolean;
  onChange: (cabecera: ComprobanteCabecera) => void;
};

const leyendaFields = [
  { key: 'leyenda1' as const, testId: 'leyenda-1', labelKey: 'pedidos.carga.leyenda.1' },
  { key: 'leyenda2' as const, testId: 'leyenda-2', labelKey: 'pedidos.carga.leyenda.2' },
  { key: 'leyenda3' as const, testId: 'leyenda-3', labelKey: 'pedidos.carga.leyenda.3' },
  { key: 'leyenda4' as const, testId: 'leyenda-4', labelKey: 'pedidos.carga.leyenda.4' },
  { key: 'leyenda5' as const, testId: 'leyenda-5', labelKey: 'pedidos.carga.leyenda.5' },
];

export function ComprobanteLeyendasPie({ cabecera, readOnly, onChange }: ComprobanteLeyendasPieProps) {
  const { t } = useTranslation();

  return (
    <section className="comprobanteLeyendasPie" data-testid="leyendas-pie">
      <h3 className="comprobanteLeyendasPie__title">{t('pedidos.carga.leyendasTitle')}</h3>
      <div className="comprobanteLeyendasPie__grid">
        {leyendaFields.map((field) => (
          <TextBox
            key={field.key}
            label={t(field.labelKey)}
            value={cabecera[field.key] ?? ''}
            readOnly={readOnly}
            onValueChanged={(event) => {
              if (!isDevExtremeUserChange(event)) {
                return;
              }

              const valor = String(event.value ?? '').trim();

              onChange({
                ...cabecera,
                [field.key]: valor !== '' ? valor : null,
              });
            }}
            inputAttr={{ 'data-testid': field.testId }}
          />
        ))}
      </div>
    </section>
  );
}
