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
          items: Array.from({ length: 1000 }, (_, index) => ({
            codArticulo: `A${index}`,
            descripcion: `Art ${index}`,
            stock: 1,
            comprometido: 0,
            disponible: 1,
          })),
          page: 1,
          page_size: 1000,
          total: 1002,
          total_pages: 2,
          metadata: { fecha_proceso: '2026-08-27T10:00:00Z' },
        },
      })
      .mockResolvedValueOnce({
        error: 0,
        respuesta: 'OK',
        resultado: {
          items: [
            { codArticulo: 'A1000', descripcion: 'Art 1000', stock: 1, comprometido: 0, disponible: 1 },
            { codArticulo: 'A1001', descripcion: 'Art 1001', stock: 1, comprometido: 0, disponible: 1 },
          ],
          page: 2,
          page_size: 1000,
          total: 1002,
          total_pages: 2,
          metadata: { fecha_proceso: '2026-08-27T10:00:00Z' },
        },
      });

    const { fetchStock } = await import('./consultaApi');
    const result = await fetchStock();

    expect(apiRequestMock).toHaveBeenCalledTimes(2);
    expect(apiRequestMock.mock.calls[0]?.[0]).toBe('/consultas/stock?page=1&page_size=1000');
    expect(apiRequestMock.mock.calls[1]?.[0]).toBe('/consultas/stock?page=2&page_size=1000');
    expect(result.items).toHaveLength(1002);
    expect(result.meta?.fecha_proceso).toBe('2026-08-27T10:00:00Z');
  });

  it('acumula todas las páginas al cargar historial de ventas con filtros', async () => {
    apiRequestMock
      .mockResolvedValueOnce({
        error: 0,
        respuesta: 'OK',
        resultado: {
          items: Array.from({ length: 1000 }, (_, index) => ({
            codCliente: 'C1',
            tipo: 'FAC',
            numero: `N${index}`,
            codArticulo: `A${index}`,
            fechaEmision: '2026-06-02',
            cantidad: 1,
          })),
          page: 1,
          page_size: 1000,
          total: 1001,
          total_pages: 2,
          metadata: { dias_ventas_detalladas: 90 },
        },
      })
      .mockResolvedValueOnce({
        error: 0,
        respuesta: 'OK',
        resultado: {
          items: [
            {
              codCliente: 'C1',
              tipo: 'FAC',
              numero: 'N1000',
              codArticulo: 'A1000',
              fechaEmision: '2026-06-02',
              cantidad: 1,
            },
          ],
          page: 2,
          page_size: 1000,
          total: 1001,
          total_pages: 2,
          metadata: { dias_ventas_detalladas: 90 },
        },
      });

    const { fetchHistorialVentas } = await import('./consultaApi');
    const result = await fetchHistorialVentas({
      fechaDesde: '2026-06-02',
      fechaHasta: '2026-06-02',
    });

    expect(apiRequestMock).toHaveBeenCalledTimes(2);
    expect(apiRequestMock.mock.calls[0]?.[0]).toBe(
      '/consultas/historial-ventas?fecha_desde=2026-06-02&fecha_hasta=2026-06-02&page=1&page_size=1000',
    );
    expect(apiRequestMock.mock.calls[1]?.[0]).toBe(
      '/consultas/historial-ventas?fecha_desde=2026-06-02&fecha_hasta=2026-06-02&page=2&page_size=1000',
    );
    expect(result.items).toHaveLength(1001);
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
