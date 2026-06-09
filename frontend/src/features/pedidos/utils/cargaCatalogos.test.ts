import { describe, expect, it } from 'vitest';
import type { ClienteOption } from '../api/comprobanteApi';
import { etiquetaCliente, ordenarClientes } from './cargaCatalogos';

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
