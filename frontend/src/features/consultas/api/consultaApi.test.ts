import { beforeEach, describe, expect, it, vi } from 'vitest';

const apiRequestMock = vi.fn();

vi.mock('../../../shared/http/client', () => ({
  apiRequest: (...args: unknown[]) => apiRequestMock(...args),
}));

describe('consultaApi fetchConsultaMapped paging', () => {
  beforeEach(() => {
    apiRequestMock.mockReset();
  });

  it('acumula todas las páginas al cargar stock', async () => {
    apiRequestMock
      .mockResolvedValueOnce({
        error: 0,
        respuesta: 'OK',
        resultado: {
          items: [
            { codArticulo: 'A1', descripcion: 'Uno', stock: 1, comprometido: 0, disponible: 1 },
            { codArticulo: 'A2', descripcion: 'Dos', stock: 2, comprometido: 0, disponible: 2 },
          ],
          page: 1,
          page_size: 1000,
          total: 3,
          total_pages: 2,
          metadata: { fecha_proceso: '2026-08-27T10:00:00Z' },
        },
      })
      .mockResolvedValueOnce({
        error: 0,
        respuesta: 'OK',
        resultado: {
          items: [{ codArticulo: 'A3', descripcion: 'Tres', stock: 3, comprometido: 0, disponible: 3 }],
          page: 2,
          page_size: 1000,
          total: 3,
          total_pages: 2,
          metadata: { fecha_proceso: '2026-08-27T10:00:00Z' },
        },
      });

    const { fetchStock } = await import('./consultaApi');
    const result = await fetchStock();

    expect(apiRequestMock).toHaveBeenCalledTimes(2);
    expect(apiRequestMock.mock.calls[0]?.[0]).toBe('/consultas/stock?page=1&page_size=1000');
    expect(apiRequestMock.mock.calls[1]?.[0]).toBe('/consultas/stock?page=2&page_size=1000');
    expect(result.items).toHaveLength(3);
    expect(result.items.map((row) => row.codArticulo)).toEqual(['A1', 'A2', 'A3']);
    expect(result.meta?.fecha_proceso).toBe('2026-08-27T10:00:00Z');
  });

  it('respeta query string existente en presupuestos', async () => {
    apiRequestMock.mockResolvedValueOnce({
      error: 0,
      respuesta: 'OK',
      resultado: {
        items: [],
        page: 1,
        page_size: 1000,
        total: 0,
        total_pages: 1,
        metadata: {},
      },
    });

    const { fetchPresupuestosActivos } = await import('./consultaApi');
    await fetchPresupuestosActivos();

    expect(apiRequestMock.mock.calls[0]?.[0]).toBe(
      '/consultas/presupuestos?estado=99&page=1&page_size=1000',
    );
  });
});
