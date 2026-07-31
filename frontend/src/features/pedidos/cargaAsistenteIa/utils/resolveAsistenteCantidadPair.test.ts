import { describe, expect, it } from 'vitest';
import { resolveAsistenteCantidadPair } from './resolveAsistenteCantidadPair';

describe('resolveAsistenteCantidadPair', () => {
  it('confía en el par cantidad/cantidadVenta cuando el backend ya lo envió', () => {
    expect(
      resolveAsistenteCantidadPair(
        { cantidad: 20, cantidadVenta: 4, equivalenciaVentas: 5 },
        true,
      ),
    ).toEqual({ cantidad: 20, cantidadVenta: 4, equivalenciaVentas: 5 });
  });

  it('deriva cantidad stock si falta cantidadVenta en modo unidades de venta', () => {
    expect(
      resolveAsistenteCantidadPair({ cantidad: 4, equivalenciaVentas: 5 }, true),
    ).toEqual({ cantidad: 20, cantidadVenta: 4, equivalenciaVentas: 5 });
  });

  it('en modo stock mantiene cantidad y deriva cantidadVenta', () => {
    expect(
      resolveAsistenteCantidadPair({ cantidad: 10, equivalenciaVentas: 2 }, false),
    ).toEqual({ cantidad: 10, cantidadVenta: 5, equivalenciaVentas: 2 });
  });
});
