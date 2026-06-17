import { describe, expect, it } from 'vitest';
import { emptyComprobanteCabecera } from '../types/comprobanteCabecera';
import {
  isPedidosCargaExcelImportDisabled,
  mapExcelRowsToRenglones,
  mapExcelRowToCabecera,
} from './mapExcelImportToCarga';
import { createEmptyRenglon } from './renglonesCarga';

describe('mapExcelImportToCarga', () => {
  it('mapea cabecera desde payload enriquecido', () => {
    const base = emptyComprobanteCabecera('CLI001');
    const cabecera = mapExcelRowToCabecera(
      {
        cod_cliente: 'CLI001',
        cod_condvta: 2,
        cod_lista: 5,
        bonif1: 3,
        bonif2: 0,
        bonif3: 0,
        cod_perfil: 'MVP',
        observaciones: 'Estas son las observaciones generales del pedido',
        leyenda1: 'Leyenda 1',
      },
      base,
    );

    expect(cabecera.codCliente).toBe('CLI001');
    expect(cabecera.codCondvta).toBe(2);
    expect(cabecera.listaPrecios).toBe(5);
    expect(cabecera.bonif1).toBe(3);
    expect(cabecera.codPerfil).toBe('MVP');
    expect(cabecera.observaciones).toBe('Estas son las observaciones generales del pedido');
    expect(cabecera.leyenda1).toBe('Leyenda 1');
  });

  it('mapea renglones numerados con porcBonif', () => {
    const renglones = mapExcelRowsToRenglones([
      {
        cod_articulo: 'ART01',
        descripcion_articulo: 'Demo',
        cantidad: 2,
        precio: 100,
        porc_bonif: 5,
        porc_iva: 21,
      },
      {
        cod_articulo: 'ART02',
        cantidad: 1,
        precio: 50,
        porc_bonif: 0,
        porc_iva: 0.21,
      },
    ]);

    expect(renglones).toHaveLength(2);
    expect(renglones[0].renglon).toBe(1);
    expect(renglones[0].porcBonif).toBe(5);
    expect(renglones[1].porcIva).toBe(21);
  });
});

describe('isPedidosCargaExcelImportDisabled', () => {
  const baseInput = {
    excelImportEnabled: true,
    readOnly: false,
    modo: 'nuevo',
    comprobanteId: null,
    renglones: [createEmptyRenglon(1)],
    isClienteProfile: false,
    selectedCliente: null,
  };

  it('habilita en modo nuevo limpio', () => {
    expect(isPedidosCargaExcelImportDisabled(baseInput)).toBe(false);
  });

  it('deshabilita con cliente seleccionado para vendedor', () => {
    expect(
      isPedidosCargaExcelImportDisabled({ ...baseInput, selectedCliente: 'CLI001' }),
    ).toBe(true);
  });

  it('permite perfil cliente con cliente precargado', () => {
    expect(
      isPedidosCargaExcelImportDisabled({
        ...baseInput,
        isClienteProfile: true,
        selectedCliente: 'CLI001',
      }),
    ).toBe(false);
  });
});
