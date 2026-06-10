import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import SelectBox from 'devextreme-react/select-box';
import TextArea from 'devextreme-react/text-area';
import { cerrarPresupuesto, fetchMotivosCierreNegativos, type MotivoCierreOption } from '../api/presupuestoApi';
import type { PresupuestoConsultaRow } from '../../consultas/api/consultaApi';

type PresupuestoCierreDialogProps = {
  visible: boolean;
  presupuesto: PresupuestoConsultaRow | null;
  onClose: () => void;
  onClosed: () => void;
};

export function PresupuestoCierreDialog({
  visible,
  presupuesto,
  onClose,
  onClosed,
}: PresupuestoCierreDialogProps) {
  const { t } = useTranslation();
  const [motivos, setMotivos] = useState<MotivoCierreOption[]>([]);
  const [selectedMotivo, setSelectedMotivo] = useState<number | null>(null);
  const [observacion, setObservacion] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  useEffect(() => {
    if (!visible) {
      return;
    }

    let mounted = true;

    const load = async () => {
      try {
        const items = await fetchMotivosCierreNegativos();
        if (mounted) {
          setMotivos(items);
          setSelectedMotivo(items[0]?.idMotivo ?? null);
        }
      } catch {
        if (mounted) {
          setMotivos([]);
          setSelectedMotivo(null);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [visible]);

  const handleConfirm = async () => {
    if (!presupuesto || selectedMotivo === null) {
      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);

    try {
      await cerrarPresupuesto(presupuesto.codPedido, selectedMotivo, observacion);
      onClosed();
      onClose();
      setObservacion('');
    } catch {
      setSubmitError(t('presupuestos.cierre.error'));
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      dragEnabled={false}
      showCloseButton={true}
      width={480}
      height={360}
      title={t('presupuestos.cierre.title')}
      elementAttr={{ 'data-testid': 'presupuestoCierreDialog' }}
    >
      <div className="presupuestoCierreDialog">
        <p>{t('presupuestos.cierre.presupuestoLabel', { numero: presupuesto?.numero ?? '' })}</p>
        <SelectBox
          dataSource={motivos}
          valueExpr="idMotivo"
          displayExpr="descripcion"
          value={selectedMotivo}
          onValueChanged={(event) => {
            setSelectedMotivo((event.value as number | null) ?? null);
          }}
          label={t('presupuestos.cierre.motivoLabel')}
          inputAttr={{ 'data-testid': 'presupuestoCierreMotivo' }}
        />
        <TextArea
          value={observacion}
          onValueChanged={(event) => {
            setObservacion(String(event.value ?? ''));
          }}
          label={t('presupuestos.cierre.observacionLabel')}
          height={90}
          inputAttr={{ 'data-testid': 'presupuestoCierreObservacion' }}
        />
        {submitError ? <p role="alert">{submitError}</p> : null}
        <div className="presupuestoCierreDialog__actions">
          <Button
            text={t('presupuestos.cierre.confirm')}
            type="default"
            disabled={isSubmitting || selectedMotivo === null}
            onClick={() => {
              void handleConfirm();
            }}
            elementAttr={{ 'data-testid': 'presupuestoCierreConfirm' }}
          />
          <Button
            text={t('presupuestos.cierre.cancel')}
            stylingMode="text"
            onClick={onClose}
            elementAttr={{ 'data-testid': 'presupuestoCierreCancel' }}
          />
        </div>
      </div>
    </Popup>
  );
}
