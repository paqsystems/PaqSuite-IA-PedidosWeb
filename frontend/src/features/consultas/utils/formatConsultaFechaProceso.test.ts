import { describe, expect, it } from 'vitest';
import { formatConsultaFechaProceso } from './formatConsultaFechaProceso';

describe('formatConsultaFechaProceso', () => {
  it('formatea fecha ISO sin segundos según locale', () => {
    const formatted = formatConsultaFechaProceso('2026-06-04T15:30:45Z', 'es-AR');

    expect(formatted).toMatch(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/);
    expect(formatted).not.toMatch(/:45/);
    expect(formatted).not.toContain(',');
  });

  it('devuelve cadena vacía cuando no hay valor', () => {
    expect(formatConsultaFechaProceso(null, 'es')).toBe('');
    expect(formatConsultaFechaProceso(undefined, 'es')).toBe('');
  });
});
