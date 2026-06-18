type SelectBoxTextSource = {
  option: (name: string) => unknown;
  field?: () => unknown;
  element?: () => Element;
};

export function readInputValueFromEditorDom(component: SelectBoxTextSource): string {
  const fieldElement = component.field?.();
  if (fieldElement instanceof HTMLInputElement) {
    return fieldElement.value;
  }

  if (fieldElement instanceof HTMLTextAreaElement) {
    return fieldElement.value;
  }

  const root = component.element?.();
  if (!(root instanceof Element)) {
    return '';
  }

  const input = root.querySelector('input.dx-texteditor-input');
  return input instanceof HTMLInputElement ? input.value : '';
}

/**
 * Texto visible en el editor. Prioriza el input DOM (texto actual del usuario)
 * sobre searchValue (puede quedar en la búsqueda anterior).
 */
export function resolveArticulosCargaSearchText(component: SelectBoxTextSource): string {
  const domValue = readInputValueFromEditorDom(component);
  if (domValue.length > 0) {
    return domValue;
  }

  const text = component.option('text');
  if (typeof text === 'string' && text.length > 0) {
    return text;
  }

  const searchValue = component.option('searchValue');
  if (typeof searchValue === 'string' && searchValue.length > 0) {
    return searchValue;
  }

  return '';
}

export function resolveArticulosCargaSearchInput(
  component: SelectBoxTextSource,
): HTMLInputElement | null {
  const fieldElement = component.field?.();
  if (fieldElement instanceof HTMLInputElement) {
    return fieldElement;
  }

  const root = component.element?.();
  if (!(root instanceof Element)) {
    return null;
  }

  const input = root.querySelector('input.dx-texteditor-input');
  return input instanceof HTMLInputElement ? input : null;
}
