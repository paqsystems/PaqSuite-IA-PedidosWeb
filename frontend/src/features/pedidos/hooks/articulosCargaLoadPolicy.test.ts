import { describe, expect, it } from 'vitest';
import {
  articulosCargaMinTypedLength,
  hasEnoughArticulosSearchText,
  hasPendingArticulosCargaQuery,
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
    expect(shouldFetchArticulosCarga('art ')).toBe(true);
    expect(shouldFetchArticulosCarga('ART-')).toBe(true);
  });

  it('bloquea búsqueda corta o vacía', () => {
    expect(shouldFetchArticulosCarga('art')).toBe(false);
    expect(shouldFetchArticulosCarga('')).toBe(false);
    expect(shouldFetchArticulosCarga('   ')).toBe(false);
  });
});

describe('hasPendingArticulosCargaQuery', () => {
  it('solo acepta consultas explícitas con texto suficiente', () => {
    expect(hasPendingArticulosCargaQuery('tosta')).toBe(true);
    expect(hasPendingArticulosCargaQuery('art')).toBe(false);
    expect(hasPendingArticulosCargaQuery(null)).toBe(false);
  });
});
