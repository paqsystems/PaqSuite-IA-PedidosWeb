import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { EmissionDialog, isNativeApp } from '@paqsuite/react-core';

type ConsultaEmitReportToolbarItemProps = {
  emissionProcessCode: string;
  disabled?: boolean;
};

const emissionEnabled =
  import.meta.env.VITE_EMISSION_ENABLED === undefined ||
  import.meta.env.VITE_EMISSION_ENABLED === 'true' ||
  import.meta.env.VITE_EMISSION_ENABLED === '1';

export function ConsultaEmitReportToolbarItem({
  emissionProcessCode,
  disabled = false,
}: ConsultaEmitReportToolbarItemProps) {
  const { t } = useTranslation();
  const [open, setOpen] = useState(false);
  const native = isNativeApp();

  if (!emissionEnabled || native) {
    return null;
  }

  return (
    <>
      <Button
        text={t('emission.emitReport')}
        icon="export"
        stylingMode="contained"
        disabled={disabled}
        onClick={() => setOpen(true)}
        elementAttr={{ 'data-testid': 'gridEmitReport' }}
      />
      {open ? (
        <EmissionDialog
          processCode={emissionProcessCode}
          open={open}
          onOpenChange={setOpen}
          isNative={false}
          disabled={disabled}
          t={(key) => t(key)}
        />
      ) : null}
    </>
  );
}
