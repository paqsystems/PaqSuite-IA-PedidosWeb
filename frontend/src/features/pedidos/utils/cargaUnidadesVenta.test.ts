import { describe, expect, it } from 'vitest';
import {
  applyCantidadUsuarioToRenglon,
  cantidadVisibleParaUsuario,
  fromCantidadUsuario,
  resolveEquivalenciaVentas,
} from './cargaUnidadesVenta';

describe('cargaUnidadesVenta', () => {
  it('resolveEquivalenciaVentas trata 0/null como 1', () => {
    expect(resolveEquivalenciaVentas(null)).toBe(1);
    expect(resolveEquivalenciaVentas(0)).toBe(1);
    expect(resolveEquivalenciaVentas(2)).toBe(2);
  });

  it('fromCantidadUsuario en modo stock deriva cantidadVenta', () => {
    expect(fromCantidadUsuario(10, 2, false)).toEqual({ cantidad: 10, cantidadVenta: 5 });
  });

  it('fromCantidadUsuario en modo venta deriva cantidad', () => {
    expect(fromCantidadUsuario(4, 2.5, true)).toEqual({ cantidad: 10, cantidadVenta: 4 });
  });

  it('cantidadVisibleParaUsuario respeta el parámetro', () => {
    expect(cantidadVisibleParaUsuario(10, 4, false)).toBe(10);
    expect(cantidadVisibleParaUsuario(10, 4, true)).toBe(4);
  });

  it('applyCantidadUsuarioToRenglon actualiza el par', () => {
    const result = applyCantidadUsuarioToRenglon(
      { cantidad: 1, cantidadVenta: 1, equivalenciaVentas: 2 },
      3,
      true,
    );
    expect(result.cantidad).toBe(6);
    expect(result.cantidadVenta).toBe(3);
  });
});
