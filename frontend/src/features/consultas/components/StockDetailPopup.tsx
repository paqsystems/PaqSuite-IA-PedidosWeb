import { useTranslation } from 'react-i18next';
import Popup from 'devextreme-react/popup';
import type { StockConsultaRow } from '../api/consultaApi';
import './stockDetailPopup.css';

type StockDetailPopupProps = {
  item: StockConsultaRow | null;
  onClose: () => void;
};

function formatAmount(value: number | null | undefined): string {
  if (value === null || value === undefined) {
    return '—';
  }

  return new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

export function StockDetailPopup({ item, onClose }: StockDetailPopupProps) {
  const { t } = useTranslation();

  return (
    <Popup
      visible={item !== null}
      onHiding={onClose}
      showTitle
      title={item?.codArticulo ?? ''}
      width={360}
      height="auto"
      dragEnabled={false}
      hideOnOutsideClick
      elementAttr={{ 'data-testid': 'stockDetailPopup' }}
    >
      {item && (
        <dl className="stockDetailPopup">
          <div>
            <dt>{t('consultas.column.descripcion')}</dt>
            <dd>{item.descripcion}</dd>
          </div>
          <div>
            <dt>{t('consultas.column.stock')}</dt>
            <dd>{formatAmount(item.stock)}</dd>
          </div>
          <div>
            <dt>{t('consultas.column.comprometido')}</dt>
            <dd>{formatAmount(item.comprometido)}</dd>
          </div>
          <div>
            <dt>{t('consultas.column.comprometidoWeb')}</dt>
            <dd>{formatAmount(item.comprometidoWeb)}</dd>
          </div>
          <div>
            <dt>{t('consultas.column.disponibleNeto')}</dt>
            <dd>{formatAmount(item.disponibleNeto)}</dd>
          </div>
          {item.codBase && (
            <>
              <div>
                <dt>{t('consultas.column.codBase')}</dt>
                <dd>{item.codBase}</dd>
              </div>
              <div>
                <dt>{t('consultas.column.stockBase')}</dt>
                <dd>{formatAmount(item.stockBase)}</dd>
              </div>
              <div>
                <dt>{t('consultas.column.comprometidoBase')}</dt>
                <dd>{formatAmount(item.comprometidoBase)}</dd>
              </div>
              <div>
                <dt>{t('consultas.column.comprometidoBaseWeb')}</dt>
                <dd>{formatAmount(item.comprometidoBaseWeb)}</dd>
              </div>
              <div>
                <dt>{t('consultas.column.disponibleNetoBase')}</dt>
                <dd>{formatAmount(item.disponibleNetoBase)}</dd>
              </div>
            </>
          )}
        </dl>
      )}
    </Popup>
  );
}
