import { describe, expect, it } from 'vitest';
import {
  articulosCargaMinTypedLength,
  hasEnoughArticulosSearchText,
  shouldFetchArticulosCarga,
} from './articulosCargaLoadPolicy';

describe('hasEnoughArticulosSearchText', () => {
  it('exige al menos 4 caracteres incluyendo espacios', () => {
    expect(articulosCargaMinTypedLength).toBe(4);
    expect(hasEnoughArticulosSearchText('art')).toBe(false);
    expect(hasEnoughArticulosSearchText('art ')).toBe(true);
    expect(hasEnoughArticulosSearchText(' art')).toBe(true);
    expect(hasEnoughArticulosSearchText('    ')).toBe(true);
    expect(hasEnoughArticulosSearchText('ART-')).toBe(true);
  });
});

describe('shouldFetchArticulosCarga', () => {
  it('permite búsqueda con texto suficiente', () => {
    expect(shouldFetchArticulosCarga('art ', false)).toBe(true);
    expect(shouldFetchArticulosCarga('ART-', false)).toBe(true);
  });

  it('bloquea búsqueda corta o vacía salvo apertura explícita del desplegable', () => {
    expect(shouldFetchArticulosCarga('art', false)).toBe(false);
    expect(shouldFetchArticulosCarga('', false)).toBe(false);
    expect(shouldFetchArticulosCarga('   ', false)).toBe(false);
    expect(shouldFetchArticulosCarga('', true)).toBe(true);
  });
});
