import { useTranslation } from 'react-i18next';
import LoadIndicator from 'devextreme-react/load-indicator';
import Popup from 'devextreme-react/popup';

type PedidosCargaArticulosStockLoadPanelProps = {
  visible: boolean;
};

export function PedidosCargaArticulosStockLoadPanel({
  visible,
}: PedidosCargaArticulosStockLoadPanelProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={visible}
      dragEnabled={false}
      hideOnOutsideClick={false}
      showCloseButton={false}
      shading={true}
      width={420}
      height="auto"
      title={t('pedidos.carga.articulosStockLoadTitulo')}
      elementAttr={{ 'data-testid': 'articulos-cargando' }}
    >
      <div className="pedidosCargaArticulosStockLoadPanel">
        <LoadIndicator height={48} width={48} />
        <p className="pedidosCargaArticulosStockLoadPanel__message">
          {t('pedidos.carga.articulosCargando')}
        </p>
        <p className="pedidosCargaArticulosStockLoadPanel__hint">
          {t('pedidos.carga.articulosStockLoadHint')}
        </p>
      </div>
    </Popup>
  );
}
