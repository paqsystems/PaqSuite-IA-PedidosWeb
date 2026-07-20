import { useTranslation } from 'react-i18next';
import LoadPanel from 'devextreme-react/load-panel';
import type { ImportacionMasivaProgreso } from '../types/importacionMasivaTypes';

type ImportacionMasivaProgresoOverlayProps = {
  progreso: ImportacionMasivaProgreso | null;
};

export function ImportacionMasivaProgresoOverlay({ progreso }: ImportacionMasivaProgresoOverlayProps) {
  const { t } = useTranslation();

  if (progreso === null) {
    return null;
  }

  return (
    <LoadPanel
      visible
      showIndicator
      showPane
      shading
      message={t('pedidos.importacionMasiva.grabandoProgreso', {
        x: progreso.x,
        n: progreso.n,
      })}
      elementAttr={{ 'data-testid': 'importacionMasivaProgreso' }}
    />
  );
}
