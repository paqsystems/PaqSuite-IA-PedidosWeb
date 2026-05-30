import { describe, expect, it } from 'vitest';
import { resolveMenuNodeType } from '../../src/features/menu/utils/resolveMenuNodeType';

describe('resolveMenuNodeType', () => {
  it('prioriza routePath para clasificar proceso', () => {
    expect(resolveMenuNodeType('/pedidos/carga', 'G')).toBe('process');
  });

  it('usa tipoProceso P cuando no hay ruta', () => {
    expect(resolveMenuNodeType(null, 'P')).toBe('process');
  });

  it('clasifica agrupador sin ruta ni tipo P', () => {
    expect(resolveMenuNodeType('', 'G')).toBe('group');
  });
});
