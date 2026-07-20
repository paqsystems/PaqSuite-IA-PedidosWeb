import { describe, expect, it } from 'vitest';
import { emptyComprobanteCabecera } from '../../types/comprobanteCabecera';
import { mergeImportacionMasivaCabeceraWithInicial } from './mergeImportacionMasivaCabeceraWithInicial';

describe('mergeImportacionMasivaCabeceraWithInicial', () => {
  it('completa lista de precios y condicion cuando el borrador las trae vacias', () => {
    const borrador = emptyComprobanteCabecera('CLI001');
    borrador.listaPrecios = null;
    borrador.codCondvta = null;
    borrador.vendedorNombre = 'Vendedor Excel';

    const inicial = emptyComprobanteCabecera('CLI001');
    inicial.listaPrecios = 1;
    inicial.listaPreciosDescripcion = 'Lista 1';
    inicial.codCondvta = 5;
    inicial.vendedorNombre = 'Vendedor Maestro';

    const merged = mergeImportacionMasivaCabeceraWithInicial(borrador, inicial);

    expect(merged.listaPrecios).toBe(1);
    expect(merged.listaPreciosDescripcion).toBe('Lista 1');
    expect(merged.codCondvta).toBe(5);
    expect(merged.vendedorNombre).toBe('Vendedor Excel');
  });

  it('conserva lista de precios del borrador cuando viene informada', () => {
    const borrador = emptyComprobanteCabecera('CLI001');
    borrador.listaPrecios = 2;
    borrador.listaPreciosDescripcion = 'Lista 2';

    const inicial = emptyComprobanteCabecera('CLI001');
    inicial.listaPrecios = 1;
    inicial.listaPreciosDescripcion = 'Lista 1';

    const merged = mergeImportacionMasivaCabeceraWithInicial(borrador, inicial);

    expect(merged.listaPrecios).toBe(2);
    expect(merged.listaPreciosDescripcion).toBe('Lista 2');
  });
});
