/**
 * SelectBox DevExtreme con soporte transversal CC PQ #3:
 * - bloqueo + hint durante carga (`isLoading`)
 * - auto-selección cuando la búsqueda devuelve un único ítem (`autoSelectSingleMatch`)
 *
 * Reutilizar en pantallas 101 (cliente, artículos) y demás combobox del shell.
 */
import SelectBox, { type ISelectBoxOptions } from 'devextreme-react/select-box';
import { useDxSelectBoxLoadState } from './useDxSelectBoxLoadState';
import { createSelectBoxAutoMatchInputHandler } from './tryAutoSelectSingleMatch';

export type SelectBoxDxProps = ISelectBoxOptions & {
  isLoading?: boolean;
  /** Si false, solo muestra hint durante carga (útil en búsqueda remota por teclado). Default true. */
  disableWhileLoading?: boolean;
  autoSelectSingleMatch?: boolean;
  /** Mínimo de caracteres para auto-selección; si no se indica, usa minSearchLength. */
  autoSelectMinSearchLength?: number;
};

export function SelectBoxDx({
  isLoading = false,
  disableWhileLoading = true,
  autoSelectSingleMatch = false,
  autoSelectMinSearchLength,
  disabled,
  hint,
  onInput,
  valueExpr = 'id',
  minSearchLength = 0,
  searchTimeout = 350,
  ...rest
}: SelectBoxDxProps) {
  const loadState = useDxSelectBoxLoadState(isLoading, disableWhileLoading);
  const handleInput = createSelectBoxAutoMatchInputHandler({
    enabled: autoSelectSingleMatch,
    valueExpr: String(valueExpr),
    minSearchLength: autoSelectMinSearchLength ?? minSearchLength,
    searchTimeout,
    userOnInput: onInput,
  });

  return (
    <SelectBox
      {...rest}
      valueExpr={valueExpr}
      minSearchLength={minSearchLength}
      searchTimeout={searchTimeout}
      disabled={Boolean(disabled) || loadState.disabled}
      hint={loadState.hint ?? hint}
      onInput={handleInput}
    />
  );
}
