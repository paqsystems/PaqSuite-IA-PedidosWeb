import { useTranslation } from 'react-i18next';
import TextArea from 'devextreme-react/text-area';
import { isDevExtremeUserChange } from '../../../../shared/ui/devextremeUserChange';
import { ComprobanteCabeceraForm } from '../ComprobanteCabeceraForm';
import { ComprobanteLeyendasPie } from '../ComprobanteLeyendasPie';
import type { usePedidosCargaMobile } from '../../hooks/usePedidosCargaMobile';
import '../../pages/PedidosCargaPage.css';

type PedidosCargaMobileCabeceraStepProps = {
  carga: ReturnType<typeof usePedidosCargaMobile>;
};

export function PedidosCargaMobileCabeceraStep({ carga }: PedidosCargaMobileCabeceraStepProps) {
  const { t } = useTranslation();

  if (!carga.cabecera) {
    return null;
  }

  return (
    <div
      className="pedidosCargaMobilePage__panel pedidosCargaMobileCabeceraStep"
      data-testid="carga-mobile-step-cabecera"
    >
      <h2 className="pedidosCargaMobilePage__panelTitle">{t('pedidos.carga.mobile.stepCabecera')}</h2>

      {carga.cabeceraLoading ? (
        <p data-testid="carga-mobile-cabecera-loading">{t('pedidos.carga.cabeceraCargando')}</p>
      ) : (
        <>
          <ComprobanteCabeceraForm
            cabecera={carga.cabecera}
            catalogos={carga.catalogos}
            parametrosCarga={carga.parametrosCarga}
            readOnly={carga.readOnly}
            clienteNombre={carga.clienteLabel}
            onChange={carga.handleCabeceraChange}
          />

          <ComprobanteLeyendasPie
            cabecera={carga.cabecera}
            readOnly={carga.readOnly}
            onChange={carga.handleCabeceraChange}
          />

          <TextArea
            label={t('pedidos.carga.cabecera.observaciones')}
            value={carga.cabecera.observaciones}
            readOnly={carga.readOnly}
            height={88}
            inputAttr={{ 'data-testid': 'carga-mobile-observaciones' }}
            onValueChanged={(event) => {
              if (!isDevExtremeUserChange(event)) {
                return;
              }

              carga.handleCabeceraChange({
                ...carga.cabecera!,
                observaciones: String(event.value ?? ''),
              });
            }}
          />
        </>
      )}
    </div>
  );
}
