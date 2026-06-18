import { describe, expect, it } from 'vitest';
import { resolveArticulosCargaSearchText } from './articulosCargaSearchText';

describe('resolveArticulosCargaSearchText', () => {
  it('lee searchValue cuando no hay input DOM', () => {
    const component = {
      option: (name: string) => (name === 'searchValue' ? 'tostada' : ''),
    };

    expect(resolveArticulosCargaSearchText(component)).toBe('tostada');
  });

  it('prioriza el input DOM sobre searchValue desactualizado', () => {
    const input = document.createElement('input');
    input.value = 'arroz';
    input.className = 'dx-texteditor-input';

    const component = {
      option: (name: string) => (name === 'searchValue' ? 'tostada' : ''),
      element: () => {
        const root = document.createElement('div');
        root.appendChild(input);
        return root;
      },
    };

    expect(resolveArticulosCargaSearchText(component)).toBe('arroz');
  });
});
