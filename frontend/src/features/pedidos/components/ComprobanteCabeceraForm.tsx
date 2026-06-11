import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import SelectBox from 'devextreme-react/select-box';
import NumberBox from 'devextreme-react/number-box';
import TextBox from 'devextreme-react/text-box';
import TextArea from 'devextreme-react/text-area';
import DateBox from 'devextreme-react/date-box';
import { isDevExtremeUserChange } from '../../../shared/ui/devextremeUserChange';
import type { ParametrosCarga } from '../api/comprobanteApi';
import {
  bonificacionCabecera3Max,
  bonificacionCabecera3Min,
  bonificacionCabeceraFormat,
  monedaCabeceraOptions,
} from '../constants/cabeceraCatalogos';
import type { CabeceraCatalogos, ComprobanteCabecera, CatalogoListaPrecios, CatalogoPerfil } from '../types/comprobanteCabecera';
import { calcularBonificacionNeta } from '../utils/renglonesCarga';

type ComprobanteCabeceraFormProps = {
  cabecera: ComprobanteCabecera;
  catalogos: CabeceraCatalogos;
  parametrosCarga: ParametrosCarga | null;
  readOnly: boolean;
  clienteNombre?: string;
  onChange: (cabecera: ComprobanteCabecera) => void;
  onListaPreciosChange?: (codLista: number | null) => void;
};

function formatListaPreciosItem(item: CatalogoListaPrecios | null): string {
  if (!item) {
    return '';
  }

  return `${item.cod_lista} — ${item.descripcion}`;
}

function formatPerfilItem(item: CatalogoPerfil | null): string {
  if (!item) {
    return '';
  }

  return `${item.cod_perfil} — ${item.descripcion}`;
}

export function ComprobanteCabeceraForm({
  cabecera,
  catalogos,
  parametrosCarga,
  readOnly,
  clienteNombre,
  onChange,
  onListaPreciosChange,
}: ComprobanteCabeceraFormProps) {
  const { t } = useTranslation();
  const modificaListaPrec = parametrosCarga?.modificaListaPrec ?? true;
  const modificaBonCli = parametrosCarga?.modificaBonCli ?? true;
  const puedeEditarBonifCabecera = !readOnly && modificaBonCli;

  const monedaDataSource = useMemo(
    () =>
      monedaCabeceraOptions.map((item) => ({
        codigo: item.codigo,
        descripcion: t(item.labelKey),
      })),
    [t],
  );

  const bonificacionNeta = useMemo(
    () => calcularBonificacionNeta(cabecera.bonif1, cabecera.bonif2, cabecera.bonif3),
    [cabecera.bonif1, cabecera.bonif2, cabecera.bonif3],
  );

  const patchCabecera = (partial: Partial<ComprobanteCabecera>) => {
    onChange({ ...cabecera, ...partial });
  };

  const handleListaPreciosChange = (codLista: number | null) => {
    const codListaNormalizado =
      codLista !== null && codLista !== undefined ? Number(codLista) : null;
    const lista = catalogos.listasPrecios.find(
      (item) => Number(item.cod_lista) === codListaNormalizado,
    );
    patchCabecera({
      listaPrecios: codListaNormalizado,
      listaPreciosDescripcion: lista?.descripcion ?? '',
      moneda: lista?.moneda ?? cabecera.moneda,
      incluyeIva: lista?.incluye_iva ?? cabecera.incluyeIva,
    });

    if (codListaNormalizado !== null && !Number.isNaN(codListaNormalizado) && codListaNormalizado > 0) {
      onListaPreciosChange?.(codListaNormalizado);
    }
  };

  const handleDireccionChange = (idDe: number | null) => {
    const direccion = catalogos.direccionesEntrega.find((item) => item.id_de === idDe);
    patchCabecera({
      idDe,
      direccionEntrega: direccion?.direccion ?? '',
    });
  };

  return (
    <div className="comprobanteCabeceraForm">
      {clienteNombre ? (
        <TextBox
          label={t('pedidos.carga.cabecera.cliente')}
          value={clienteNombre}
          readOnly={true}
          inputAttr={{ 'data-testid': 'cliente-fijo-label' }}
        />
      ) : null}

      <TextBox
        label={t('pedidos.carga.cabecera.vendedor')}
        value={cabecera.vendedorNombre || cabecera.codVended || ''}
        readOnly={true}
        inputAttr={{ 'data-testid': 'cabecera-vendedor' }}
      />

      <SelectBox
        label={t('pedidos.carga.cabecera.perfil')}
        dataSource={catalogos.perfiles}
        valueExpr="cod_perfil"
        displayExpr={formatPerfilItem}
        searchEnabled={true}
        searchExpr={['cod_perfil', 'descripcion']}
        showClearButton={false}
        value={cabecera.codPerfil}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ codPerfil: (event.value as string | null) ?? null });
        }}
        elementAttr={{ 'data-testid': 'cabecera-perfil' }}
      />

      <SelectBox
        label={t('pedidos.carga.cabecera.condicionVenta')}
        dataSource={catalogos.condicionesVenta}
        valueExpr="codigo"
        displayExpr="descripcion"
        value={cabecera.codCondvta}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ codCondvta: (event.value as number | null) ?? null });
        }}
        inputAttr={{ 'data-testid': 'cabecera-condicion-venta' }}
      />

      <SelectBox
        label={t('pedidos.carga.cabecera.transporte')}
        dataSource={catalogos.transportes}
        valueExpr="codigo"
        displayExpr="descripcion"
        value={cabecera.codTranspor}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ codTranspor: (event.value as string | null) ?? null });
        }}
        inputAttr={{ 'data-testid': 'cabecera-transporte' }}
      />

      <SelectBox
        label={t('pedidos.carga.cabecera.direccionEntrega')}
        dataSource={catalogos.direccionesEntrega}
        valueExpr="id_de"
        displayExpr={(item: { direccion?: string; localidad?: string } | null) =>
          item ? `${item.direccion ?? ''}${item.localidad ? ` — ${item.localidad}` : ''}` : ''
        }
        value={cabecera.idDe}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          handleDireccionChange((event.value as number | null) ?? null);
        }}
        inputAttr={{ 'data-testid': 'cabecera-direccion-entrega' }}
      />

      <SelectBox
        label={t('pedidos.carga.cabecera.listaPrecios')}
        dataSource={catalogos.listasPrecios}
        valueExpr="cod_lista"
        displayExpr={formatListaPreciosItem}
        searchEnabled={true}
        searchExpr={['cod_lista', 'descripcion']}
        showClearButton={false}
        value={cabecera.listaPrecios}
        disabled={readOnly || !modificaListaPrec}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          handleListaPreciosChange((event.value as number | null) ?? null);
        }}
        elementAttr={{ 'data-testid': 'cabecera-lista-precios' }}
      />

      <NumberBox
        label={t('pedidos.carga.cabecera.nivel')}
        value={cabecera.nivel}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ nivel: Number(event.value ?? 0) });
        }}
        inputAttr={{ 'data-testid': 'cabecera-nivel' }}
      />

      <SelectBox
        label={t('pedidos.carga.cabecera.moneda')}
        dataSource={monedaDataSource}
        valueExpr="codigo"
        displayExpr="descripcion"
        value={cabecera.moneda}
        readOnly={true}
        inputAttr={{ 'data-testid': 'cabecera-moneda' }}
      />

      <TextBox
        label={t('pedidos.carga.cabecera.incluyeIva')}
        value={cabecera.incluyeIva ? t('pedidos.carga.cabecera.si') : t('pedidos.carga.cabecera.no')}
        readOnly={true}
        inputAttr={{ 'data-testid': 'cabecera-incluye-iva' }}
      />

      <NumberBox
        label={t('pedidos.carga.cabecera.bonif1')}
        value={cabecera.bonif1}
        format={bonificacionCabeceraFormat}
        min={0}
        step={0.01}
        showSpinButtons={true}
        disabled={!puedeEditarBonifCabecera}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ bonif1: Number(event.value ?? 0) });
        }}
        inputAttr={{ 'data-testid': 'cabecera-bonif-1' }}
      />

      <NumberBox
        label={t('pedidos.carga.cabecera.bonif2')}
        value={cabecera.bonif2}
        format={bonificacionCabeceraFormat}
        min={0}
        step={0.01}
        showSpinButtons={true}
        disabled={!puedeEditarBonifCabecera}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ bonif2: Number(event.value ?? 0) });
        }}
        inputAttr={{ 'data-testid': 'cabecera-bonif-2' }}
      />

      <NumberBox
        label={t('pedidos.carga.cabecera.bonif3')}
        value={cabecera.bonif3}
        format={bonificacionCabeceraFormat}
        min={bonificacionCabecera3Min}
        max={bonificacionCabecera3Max}
        step={0.01}
        showSpinButtons={true}
        disabled={!puedeEditarBonifCabecera}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ bonif3: Number(event.value ?? 0) });
        }}
        inputAttr={{ 'data-testid': 'cabecera-bonif-3' }}
      />

      <TextBox
        label={t('pedidos.carga.cabecera.bonificacionNeta')}
        value={bonificacionNeta.toFixed(2)}
        readOnly={true}
        inputAttr={{ 'data-testid': 'cabecera-bonificacion-neta' }}
      />

      <TextBox
        label={t('pedidos.carga.cabecera.expreso')}
        value={cabecera.expreso ?? ''}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ expreso: String(event.value ?? '') || null });
        }}
        inputAttr={{ 'data-testid': 'cabecera-expreso' }}
      />

      <TextBox
        label={t('pedidos.carga.cabecera.expresoDire')}
        value={cabecera.expresoDire ?? ''}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ expresoDire: String(event.value ?? '') || null });
        }}
        inputAttr={{ 'data-testid': 'cabecera-expreso-dire' }}
      />

      <DateBox
        label={t('pedidos.carga.cabecera.fechaEntrega')}
        value={cabecera.fechaEntrega ? new Date(cabecera.fechaEntrega) : null}
        type="date"
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          const value = event.value;
          patchCabecera({
            fechaEntrega: value instanceof Date ? value.toISOString() : null,
          });
        }}
        inputAttr={{ 'data-testid': 'cabecera-fecha-entrega' }}
      />

      <TextArea
        label={t('pedidos.carga.cabecera.observaciones')}
        value={cabecera.observaciones}
        height={72}
        readOnly={readOnly}
        onValueChanged={(event) => {
          if (!isDevExtremeUserChange(event)) {
            return;
          }

          patchCabecera({ observaciones: String(event.value ?? '') });
        }}
        inputAttr={{ 'data-testid': 'cabecera-observaciones' }}
      />
    </div>
  );
}
