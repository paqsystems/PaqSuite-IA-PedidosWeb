import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import SelectBox from 'devextreme-react/select-box';
import type { GridLayoutListItem, GridLayoutSelectItem } from '../model/gridLayoutTypes';
import './gridLayoutToolbar.css';

type GridLayoutToolbarProps = {
  layouts: GridLayoutListItem[];
  selectedLayoutId: number | null;
  saveEnabled: boolean;
  canDelete: boolean;
  onSelectLayout: (layoutId: number | null) => void;
  onSave: () => void;
  onSaveAs: () => void;
  onDelete: () => void;
};

export function GridLayoutToolbar({
  layouts,
  selectedLayoutId,
  saveEnabled,
  canDelete,
  onSelectLayout,
  onSave,
  onSaveAs,
  onDelete,
}: GridLayoutToolbarProps) {
  const { t } = useTranslation();

  const selectItems = useMemo<GridLayoutSelectItem[]>(
    () => [
      { id: null, layoutName: t('gridLayout.systemTemplate') },
      ...layouts.map((layout) => ({
        id: layout.id,
        layoutName: layout.layoutName,
      })),
    ],
    [layouts, t],
  );

  return (
    <div className="gridLayoutToolbar" data-testid="gridLayoutToolbar">
      <SelectBox
        dataSource={selectItems}
        valueExpr="id"
        displayExpr="layoutName"
        value={selectedLayoutId}
        width={220}
        stylingMode="outlined"
        inputAttr={{
          'data-testid': 'gridLayoutSelect',
          'aria-label': t('gridLayout.selectLabel'),
        }}
        onValueChanged={(event) => {
          const nextId = event.value as number | null | undefined;
          onSelectLayout(nextId ?? null);
        }}
      />
      <Button
        text={t('gridLayout.save')}
        stylingMode="outlined"
        disabled={!saveEnabled}
        onClick={onSave}
        elementAttr={{ 'data-testid': 'gridLayoutSave' }}
      />
      <Button
        text={t('gridLayout.saveAs')}
        stylingMode="outlined"
        onClick={onSaveAs}
        elementAttr={{ 'data-testid': 'gridLayoutSaveAs' }}
      />
      <Button
        text={t('gridLayout.delete')}
        stylingMode="outlined"
        disabled={!canDelete}
        onClick={onDelete}
        elementAttr={{ 'data-testid': 'gridLayoutDelete' }}
      />
    </div>
  );
}
