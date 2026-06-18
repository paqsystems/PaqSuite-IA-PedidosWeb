import { describe, expect, it } from 'vitest';
import { buildHostResultFromLot } from './excelImportApi';

describe('buildHostResultFromLot', () => {
  it('arma payload onComplete con filas validas y meta del lote', () => {
    const result = buildHostResultFromLot(
      {
        guidImportacion: 'guid-1',
        estadoImportacion: 'procesada',
        esAsincronica: false,
        cantidadFilasLeidas: 3,
        cantidadFilasDescartadas: 0,
        cantidadFilasValidas: 2,
        cantidadFilasConError: 1,
        codigoProceso: 'ARTICULOS_ALTA',
        archivoOriginalNombre: 'lote.xlsx',
        permiteProcesamientoParcial: true,
      },
      [{ codigo: 'A1' }, { codigo: 'A2' }],
    );

    expect(result.guidImportacion).toBe('guid-1');
    expect(result.codigoProceso).toBe('ARTICULOS_ALTA');
    expect(result.validRows).toHaveLength(2);
    expect(result.meta).toEqual({
      totalFilas: 3,
      filasValidas: 2,
      filasConError: 1,
      permiteProcesamientoParcial: true,
      estadoImportacion: 'procesada',
      nombreArchivoOriginal: 'lote.xlsx',
    });
  });

  it('permite validRows vacio cuando el host no recibe datos', () => {
    const result = buildHostResultFromLot(
      {
        guidImportacion: 'guid-2',
        estadoImportacion: 'lista_para_procesar',
        esAsincronica: false,
        cantidadFilasLeidas: 2,
        cantidadFilasDescartadas: 0,
        cantidadFilasValidas: 0,
        cantidadFilasConError: 2,
        codigoProceso: 'ARTICULOS_ALTA',
        archivoOriginalNombre: 'errores.xlsx',
        permiteProcesamientoParcial: false,
      },
      [],
    );

    expect(result.validRows).toEqual([]);
    expect(result.meta.filasConError).toBe(2);
  });
});
