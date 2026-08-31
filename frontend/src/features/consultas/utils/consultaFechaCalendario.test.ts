import { describe, expect, it } from 'vitest';
import {
  parseLocalCalendarDate,
  toConsultaFechaCalendario,
} from './consultaFechaCalendario';

describe('consultaFechaCalendario', () => {
  it('extrae YYYY-MM-DD de ISO UTC sin corrimiento de día', () => {
    expect(toConsultaFechaCalendario('2026-06-02T00:00:00.000000Z')).toBe('2026-06-02');
    expect(toConsultaFechaCalendario('2026-06-01T00:00:00+00:00')).toBe('2026-06-01');
  });

  it('conserva date-only', () => {
    expect(toConsultaFechaCalendario('2026-06-02')).toBe('2026-06-02');
  });

  it('parsea a Date local del mismo día de calendario', () => {
    const date = parseLocalCalendarDate('2026-06-02T00:00:00.000000Z');
    expect(date).not.toBeNull();
    expect(date!.getFullYear()).toBe(2026);
    expect(date!.getMonth()).toBe(5);
    expect(date!.getDate()).toBe(2);
  });
});
