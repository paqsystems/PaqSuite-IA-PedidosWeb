import { describe, expect, it } from 'vitest';
import { formatHistorialFechaParam } from './formatHistorialFechaParam';

describe('formatHistorialFechaParam', () => {
  it('pasa strings yyyy-MM-dd sin cambiar el día', () => {
    expect(formatHistorialFechaParam('2026-06-01')).toBe('2026-06-01');
    expect(formatHistorialFechaParam('2026-06-02T00:00:00')).toBe('2026-06-02');
  });

  it('interpreta dd/MM/yyyy como día/mes/año', () => {
    expect(formatHistorialFechaParam('01/06/2026')).toBe('2026-06-01');
    expect(formatHistorialFechaParam('02/06/2026')).toBe('2026-06-02');
  });

  it('no retrocede un día con Date en medianoche UTC (Argentina UTC-3)', () => {
    // Selección calendario 01/06/2026 tipicamente llega como UTC midnight
    const utcMidnight = new Date(Date.UTC(2026, 5, 1, 0, 0, 0, 0));
    expect(formatHistorialFechaParam(utcMidnight)).toBe('2026-06-01');
  });

  it('respeta Date local cuando no es medianoche UTC', () => {
    const localAfternoon = new Date(2026, 5, 2, 15, 30, 0, 0);
    expect(formatHistorialFechaParam(localAfternoon)).toBe('2026-06-02');
  });

  it('devuelve undefined para vacíos e inválidos', () => {
    expect(formatHistorialFechaParam(null)).toBeUndefined();
    expect(formatHistorialFechaParam('')).toBeUndefined();
    expect(formatHistorialFechaParam('no-fecha')).toBeUndefined();
  });
});
