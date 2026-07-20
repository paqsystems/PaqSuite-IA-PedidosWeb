import { describe, expect, it } from 'vitest';
import { mapGrupoToBorradorFila } from './mapGrupoToBorradorFila';

describe('mapGrupoToBorradorFila', () => {
  it('mapea cabecera, renglones y totales desde grupo API', () => {
    const fila = mapGrupoToBorradorFila(
      {
        idGrupo: 'tmp-1',
        clave: { codCliente: 'CLI001', codVended: 'V1', nivel: 0 },
        cabecera: {
          cod_cliente: 'CLI001',
          cod_condvta: 1,
          bonif_1: 0,
          bonif_2: 0,
          bonif_3: 0,
          lista_precios: 1,
        },
        renglones: [
          {
            cod_articulo: 'ART1',
            descripcion_articulo: 'Articulo',
            cantidad: 2,
            precio: 50,
            porc_bonif: 0,
            porc_iva: 21,
          },
        ],
        vendedor: { codVended: 'V1', nombre: 'Vendedor Uno' },
      },
      'id-interno-1',
    );

    expect(fila.idInterno).toBe('id-interno-1');
    expect(fila.esPedido).toBe(true);
    expect(fila.cabecera.codCliente).toBe('CLI001');
    expect(fila.cabecera.vendedorNombre).toBe('Vendedor Uno');
    expect(fila.cantidadRenglones).toBe(1);
    expect(fila.renglones[0]?.codArticulo).toBe('ART1');
    expect(fila.totalImporte).toBeGreaterThan(0);
  });
});
