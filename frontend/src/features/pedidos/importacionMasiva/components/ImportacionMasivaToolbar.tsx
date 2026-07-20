import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { ExcelImportHostToolbar } from '../../../excelImport/components/ExcelImportHostToolbar';
import { EXCEL_PROCESO_PEDIDO_MASIVO } from '../constants';

type ImportacionMasivaToolbarProps = {
  excelImportEnabled: boolean;
  disabled: boolean;
  tieneFilas: boolean;
  onImportComplete: Parameters<typeof ExcelImportHostToolbar>[0]['onComplete'];
  onMarcarPedidos: () => void;
  onMarcarPresupuestos: () => void;
  onGrabar: () => void;
};

export function ImportacionMasivaToolbar({
  excelImportEnabled,
  disabled,
  tieneFilas,
  onImportComplete,
  onMarcarPedidos,
  onMarcarPresupuestos,
  onGrabar,
}: ImportacionMasivaToolbarProps) {
  const { t } = useTranslation();

  return (
    <div className="importacionMasivaPage__toolbar">
      {excelImportEnabled ? (
        <ExcelImportHostToolbar
          codigoProceso={EXCEL_PROCESO_PEDIDO_MASIVO}
          disabled={disabled}
          onComplete={onImportComplete}
        />
      ) : null}
      <Button
        text={t('pedidos.importacionMasiva.marcarPedidos')}
        stylingMode="outlined"
        disabled={disabled || !tieneFilas}
        onClick={onMarcarPedidos}
        elementAttr={{ 'data-testid': 'importacionMasivaMarcarPedidos' }}
      />
      <Button
        text={t('pedidos.importacionMasiva.marcarPresupuestos')}
        stylingMode="outlined"
        disabled={disabled || !tieneFilas}
        onClick={onMarcarPresupuestos}
        elementAttr={{ 'data-testid': 'importacionMasivaMarcarPresupuestos' }}
      />
      <Button
        text={t('pedidos.importacionMasiva.grabar')}
        type="default"
        disabled={disabled || !tieneFilas}
        onClick={onGrabar}
        elementAttr={{ 'data-testid': 'importacionMasivaGrabar' }}
      />
    </div>
  );
}
