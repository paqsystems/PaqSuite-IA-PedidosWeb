import { describe, expect, it } from 'vitest';
import type { ArticuloOption, ClienteOption } from '../api/comprobanteApi';
import { etiquetaCliente, etiquetaArticulo, ordenarClientes } from './cargaCatalogos';

const clientesFixture: ClienteOption[] = [
  {
    codCliente: 'B002',
    nombre: 'Beta Comercial',
    razonSocial: 'Beta SA',
    nombreFantasia: 'La Beta',
  },
  {
    codCliente: 'A001',
    nombre: 'Alfa Comercial',
    razonSocial: 'Alfa SA',
    nombreFantasia: 'La Alfa',
  },
];

describe('cargaCatalogos clientes', () => {
  it('formatea etiqueta con codigo, razon social y nombre fantasia', () => {
    expect(etiquetaCliente(clientesFixture[0])).toBe('(B002) Beta SA - La Beta');
  });

  it('ordena por codigo, razon social o nombre fantasia', () => {
    expect(ordenarClientes(clientesFixture, 'codCliente').map((item) => item.codCliente)).toEqual([
      'A001',
      'B002',
    ]);
    expect(ordenarClientes(clientesFixture, 'razonSocial').map((item) => item.codCliente)).toEqual([
      'A001',
      'B002',
    ]);
    expect(ordenarClientes(clientesFixture, 'nombreFantasia').map((item) => item.codCliente)).toEqual([
      'A001',
      'B002',
    ]);
  });
});

describe('etiquetaArticulo', () => {
  const t = ((key: string, params?: Record<string, string>) => {
    if (key === 'pedidos.carga.articuloDisplay' && params) {
      return `${params.codigo} - ${params.descripcion} — Disp. ${params.disponible}`;
    }
    return key;
  }) as import('i18next').TFunction;

  const articulo: ArticuloOption = {
    codArticulo: 'ART-01',
    descripcion: 'Tornillo hexagonal',
    disponibleNeto: 12.5,
    disponibleNetoBase: 3,
    precio: 100,
    bonificacion: 0,
    porcIva: 21,
  };

  it('muestra codigo, descripcion y disponible neto sin parentesis de base', () => {
    expect(etiquetaArticulo(articulo, t)).toBe('ART-01 - Tornillo hexagonal — Disp. 12,50');
  });
});
