import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import type { PivotLayoutListItem, PivotLayoutSelectItem } from '../model/pivotLayoutTypes';
import './pivotLayoutToolbar.css';

type PivotLayoutToolbarProps = {
  layouts: PivotLayoutListItem[];
  selectedConfigId: number | null;
  saveEnabled: boolean;
  canDelete: boolean;
  onSelectLayout: (configId: number | null) => void;
  onSave: () => void;
  onSaveAs: () => void;
  onDelete: () => void;
};

export function PivotLayoutToolbar({
  layouts,
  selectedConfigId,
  saveEnabled,
  canDelete,
  onSelectLayout,
  onSave,
  onSaveAs,
  onDelete,
}: PivotLayoutToolbarProps) {
  const { t } = useTranslation();

  const selectItems = useMemo<PivotLayoutSelectItem[]>(
    () => [
      { configId: null, nombre: t('pivotLayout.initialTemplate') },
      ...layouts.map((layout) => ({
        configId: layout.configId,
        nombre: layout.isOwner
          ? `${layout.nombre}${t('pivotLayout.ownerMarker')}`
          : layout.nombre,
      })),
    ],
    [layouts, t],
  );

  return (
    <div className="pivotLayoutToolbar" data-testid="pivotLayoutToolbar">
      <SelectBox
        dataSource={selectItems}
        valueExpr="configId"
        displayExpr="nombre"
        value={selectedConfigId}
        width={220}
        stylingMode="outlined"
        inputAttr={{
          'data-testid': 'pivotLayoutSelect',
          'aria-label': t('pivotLayout.selectLabel'),
        }}
        onValueChanged={(event) => {
          const nextId = event.value as number | null | undefined;
          onSelectLayout(nextId ?? null);
        }}
      />
      <Button
        text={t('pivotLayout.save')}
        stylingMode="outlined"
        disabled={!saveEnabled}
        onClick={onSave}
        elementAttr={{ 'data-testid': 'pivotLayoutSave' }}
      />
      <Button
        text={t('pivotLayout.saveAs')}
        stylingMode="outlined"
        onClick={onSaveAs}
        elementAttr={{ 'data-testid': 'pivotLayoutSaveAs' }}
      />
      <Button
        text={t('pivotLayout.delete')}
        stylingMode="outlined"
        disabled={!canDelete}
        onClick={onDelete}
        elementAttr={{ 'data-testid': 'pivotLayoutDelete' }}
      />
    </div>
  );
}
