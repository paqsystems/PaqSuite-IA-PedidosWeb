import { describe, expect, it } from 'vitest';
import {
  buildDeudaSaldoResumen,
  isComprobanteDeudaVencido,
  resolveDeudaSaldoCellTone,
} from './deudaPresentacion';
import type { DeudaConsultaRow } from '../api/consultaApi';

const hoy = new Date('2026-08-28T12:00:00');

function fila(partial: Partial<DeudaConsultaRow> & Pick<DeudaConsultaRow, 'saldo'>): DeudaConsultaRow {
  return {
    id: '1',
    codCliente: 'C1',
    razonSocial: 'Cliente',
    tipo: 'FA',
    numero: '1',
    fecha: '2026-08-01',
    vencimiento: '2026-08-20',
    ...partial,
  };
}

describe('deudaPresentacion', () => {
  it('marca saldo neto a favor en verde', () => {
    const resumen = buildDeudaSaldoResumen([fila({ saldo: -10 })], hoy);
    expect(resumen.tone).toBe('favor');
    expect(resumen.saldoNeto).toBe(-10);
  });

  it('marca saldo pendiente sin vencidos en negro', () => {
    const resumen = buildDeudaSaldoResumen(
      [fila({ saldo: 100, vencimiento: '2026-09-01' })],
      hoy,
    );
    expect(resumen.tone).toBe('pendiente');
  });

  it('marca saldo con vencidos en rojo', () => {
    const resumen = buildDeudaSaldoResumen(
      [fila({ saldo: 50, vencimiento: '2026-08-01' })],
      hoy,
    );
    expect(resumen.tone).toBe('vencido');
    expect(resumen.tieneVencidos).toBe(true);
  });

  it('no considera vencido saldo cero o a favor', () => {
    expect(isComprobanteDeudaVencido(fila({ saldo: 0 }), hoy)).toBe(false);
    expect(isComprobanteDeudaVencido(fila({ saldo: -1 }), hoy)).toBe(false);
  });

  it('colorea celda a favor en verde', () => {
    expect(resolveDeudaSaldoCellTone(fila({ saldo: -5 }), hoy)).toBe('favor');
  });

  it('colorea celda vencida en rojo', () => {
    expect(resolveDeudaSaldoCellTone(fila({ saldo: 5, vencimiento: '2026-08-01' }), hoy)).toBe(
      'vencido',
    );
  });
});
