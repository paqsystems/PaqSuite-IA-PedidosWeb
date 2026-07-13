import { describe, expect, it } from 'vitest';
import {
  appendShowConsultaToReply,
  formatShowConsulta,
} from './formatShowConsulta';

const translate = (key: string, options?: Record<string, unknown>) => {
  if (key === 'pedidos.carga.asistente.consulta.col.nro') return 'Nro';
  if (key === 'pedidos.carga.asistente.consulta.col.fecha') return 'Fecha';
  if (key === 'pedidos.carga.asistente.consulta.col.importe') return 'Importe';
  if (key === 'pedidos.carga.asistente.consulta.col.tipoNro') return 'Tipo/Nro';
  if (key === 'pedidos.carga.asistente.consulta.col.vencimiento') return 'Vencimiento';
  if (key === 'pedidos.carga.asistente.consulta.col.saldo') return 'Saldo';
  if (key === 'pedidos.carga.asistente.consulta.totales') return 'Totales';
  if (key === 'pedidos.carga.asistente.consulta.truncated') {
    return `Mostrando ${options?.shown} de ${options?.total}. Abrí la consulta del menú para ver el resto.`;
  }
  return key;
};

describe('formatShowConsulta', () => {
  it('formatea cheques con columnas nro fecha importe', () => {
    const text = formatShowConsulta(
      {
        kind: 'cheques',
        columns: ['nro', 'fecha', 'importe'],
        total: 1,
        items: [{ nro: '123', fecha: '2026-07-13', importe: 1500.5 }],
      },
      translate,
    );

    expect(text).toMatch(/Nro\s+\|\s+Fecha\s+\|\s+Importe/);
    expect(text).toContain('2026-07-13');
    expect(text).toContain('1500.50');
  });

  it('muestra fechas sin horario y total de importe si hay más de un cheque', () => {
    const text = formatShowConsulta(
      {
        kind: 'cheques',
        columns: ['nro', 'fecha', 'importe'],
        total: 2,
        totals: { importe: 350 },
        items: [
          { nro: '1', fecha: '2026-07-13T15:30:00.000000Z', importe: 100 },
          { nro: '2', fecha: '2026-07-14 00:00:00', importe: 250 },
        ],
      },
      translate,
    );

    expect(text).toContain('2026-07-13');
    expect(text).toContain('2026-07-14');
    expect(text).not.toContain('15:30');
    expect(text).toContain('Totales: Importe: 350');
  });

  it('formatea deuda y avisa si hay más filas que el tope', () => {
    const text = formatShowConsulta(
      {
        kind: 'deuda',
        columns: ['tipoNro', 'fecha', 'vencimiento', 'saldo'],
        total: 12,
        totals: { saldo: 100 },
        items: [
          {
            tipoNro: 'FC A 1',
            fecha: '2026-01-01T00:00:00Z',
            vencimiento: '2026-02-01T12:00:00Z',
            saldo: 100,
          },
        ],
      },
      translate,
    );

    expect(text).toContain('Tipo/Nro');
    expect(text).toContain('2026-01-01');
    expect(text).toContain('2026-02-01');
    expect(text).toContain('Mostrando 1 de 12');
    expect(text).toContain('Totales: Saldo: 100');
  });

  it('alinea columnas con padding en formato texto', () => {
    const text = formatShowConsulta(
      {
        kind: 'deuda',
        columns: ['tipoNro', 'fecha', 'saldo'],
        total: 2,
        items: [
          { tipoNro: 'FAC A0000300034171', fecha: '2026-06-29', saldo: 634601.5 },
          { tipoNro: 'REC  0000200041340', fecha: '2026-05-08', saldo: -1121022.5 },
        ],
      },
      translate,
    );

    const lines = text.split('\n');
    expect(lines[0]?.indexOf('|')).toBe(lines[1]?.indexOf('|'));
    expect(lines[1]?.indexOf('|')).toBe(lines[2]?.indexOf('|'));
  });

  it('appendShowConsultaToReply agrega el detalle al replyText', () => {
    const text = appendShowConsultaToReply(
      'Cheques: 1 ítem(s).',
      [
        {
          action: 'showConsulta',
          resultado: 'ok',
          payload: {
            kind: 'cheques',
            columns: ['nro', 'fecha', 'importe'],
            total: 1,
            items: [{ nro: '99', fecha: '2026-07-01', importe: 10 }],
          },
        },
      ],
      translate,
    );

    expect(text.startsWith('Cheques: 1 ítem(s).')).toBe(true);
    expect(text).toContain('2026-07-01');
    expect(text).toContain('10');
  });
});
