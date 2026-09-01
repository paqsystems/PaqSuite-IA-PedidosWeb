import { describe, expect, it } from 'vitest';
import { leyendaMaxCaracteres, recortarLeyendaCabecera } from './recortarLeyendaCabecera';

describe('recortarLeyendaCabecera', () => {
  it('conserva null y vacío', () => {
    expect(recortarLeyendaCabecera(null)).toBeNull();
    expect(recortarLeyendaCabecera('')).toBeNull();
    expect(recortarLeyendaCabecera('   ')).toBeNull();
  });

  it('conserva hasta 60 caracteres', () => {
    const exacto = 'a'.repeat(leyendaMaxCaracteres);
    expect(recortarLeyendaCabecera(exacto)).toBe(exacto);
  });

  it('recorta textos más largos', () => {
    const largo = 'b'.repeat(leyendaMaxCaracteres + 1);
    expect(recortarLeyendaCabecera(largo)).toBe('b'.repeat(leyendaMaxCaracteres));
  });
});
