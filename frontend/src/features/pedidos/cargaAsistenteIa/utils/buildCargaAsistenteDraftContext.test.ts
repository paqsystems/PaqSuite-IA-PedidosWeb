import { describe, expect, it } from 'vitest';
import {
  buildCargaAsistenteDraftContext,
  mapFunctionalProfileToPerfilUsuario,
} from './buildCargaAsistenteDraftContext';

describe('buildCargaAsistenteDraftContext', () => {
  it('builds draftContext with codLista from cabecera.listaPrecios', () => {
    const draft = buildCargaAsistenteDraftContext({
      selectedCliente: 'C001',
      cabecera: {
        listaPrecios: 3,
        observaciones: 'Obs',
        nivel: 1,
      },
      renglones: [
        {
          codArticulo: 'A1',
          cantidad: 2,
          precio: 10,
          porcBonif: 5,
          descripcionArticulo: 'Articulo 1',
        },
        {
          codArticulo: '',
          cantidad: 1,
        },
      ],
      readOnly: false,
      modo: 'nuevo',
      perfilUsuario: 'V',
    });

    expect(draft.codCliente).toBe('C001');
    expect(draft.codLista).toBe(3);
    expect(draft.modo).toBe('nuevo');
    expect(draft.renglones).toHaveLength(1);
    expect(draft.renglones[0]).toMatchObject({
      codArticulo: 'A1',
      cantidad: 2,
      descripcion: 'Articulo 1',
    });
  });

  it('maps readOnly and editar modes', () => {
    expect(
      buildCargaAsistenteDraftContext({
        selectedCliente: null,
        cabecera: null,
        renglones: [],
        readOnly: true,
        modo: 'ver',
        perfilUsuario: 'C',
      }).modo,
    ).toBe('soloLectura');

    expect(
      buildCargaAsistenteDraftContext({
        selectedCliente: null,
        cabecera: null,
        renglones: [],
        readOnly: false,
        modo: 'editar',
        perfilUsuario: 'S',
      }).modo,
    ).toBe('edicion');
  });
});

describe('mapFunctionalProfileToPerfilUsuario', () => {
  it('maps cliente, supervisor and default vendor', () => {
    expect(mapFunctionalProfileToPerfilUsuario('cliente')).toBe('C');
    expect(mapFunctionalProfileToPerfilUsuario('supervisor')).toBe('S');
    expect(mapFunctionalProfileToPerfilUsuario('vendedor')).toBe('V');
  });
});
