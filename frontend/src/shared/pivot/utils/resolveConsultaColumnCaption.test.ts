import { describe, expect, it } from 'vitest';
import { resolveConsultaColumnCaption } from './resolveConsultaColumnCaption';

describe('resolveConsultaColumnCaption', () => {
  const translate = (key: string) => {
    if (key === 'consultas.column.stock') {
      return 'Stock traducido';
    }

    return key;
  };

  it('usa clave i18n cuando existe', () => {
    expect(resolveConsultaColumnCaption(translate, 'stock', 'Stock ERP')).toBe('Stock traducido');
  });

  it('usa fallback cuando no hay traduccion', () => {
    expect(resolveConsultaColumnCaption(translate, 'campoDesconocido', 'Etiqueta ERP')).toBe('Etiqueta ERP');
  });
});
