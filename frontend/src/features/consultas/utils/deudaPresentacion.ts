import type { DeudaConsultaRow } from '../api/consultaApi';

export type DeudaSaldoTone = 'favor' | 'pendiente' | 'vencido';

export type DeudaSaldoResumen = {
  saldoNeto: number;
  tone: DeudaSaldoTone;
  tieneVencidos: boolean;
  filas: DeudaConsultaRow[];
};

function startOfToday(): Date {
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  return hoy;
}

export function parseDeudaFecha(value: string | null | undefined): Date | null {
  if (!value) {
    return null;
  }

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return null;
  }

  parsed.setHours(0, 0, 0, 0);
  return parsed;
}

export function isComprobanteDeudaVencido(
  row: Pick<DeudaConsultaRow, 'saldo' | 'vencimiento'>,
  hoy: Date = startOfToday(),
): boolean {
  if (row.saldo <= 0) {
    return false;
  }

  const vencimiento = parseDeudaFecha(row.vencimiento);
  if (!vencimiento) {
    return false;
  }

  return vencimiento < hoy;
}

export function resolveDeudaSaldoTone(
  saldo: number,
  vencido: boolean,
): DeudaSaldoTone {
  if (saldo <= 0) {
    return 'favor';
  }

  if (vencido) {
    return 'vencido';
  }

  return 'pendiente';
}

export function resolveDeudaSaldoCellTone(
  row: Pick<DeudaConsultaRow, 'saldo' | 'vencimiento'>,
  hoy: Date = startOfToday(),
): DeudaSaldoTone {
  if (row.saldo < 0) {
    return 'favor';
  }

  if (isComprobanteDeudaVencido(row, hoy)) {
    return 'vencido';
  }

  return 'pendiente';
}

export function buildDeudaSaldoResumen(
  filas: DeudaConsultaRow[],
  hoy: Date = startOfToday(),
): DeudaSaldoResumen {
  const saldoNeto = Math.round(filas.reduce((total, fila) => total + fila.saldo, 0) * 100) / 100;
  const tieneVencidos = filas.some((fila) => isComprobanteDeudaVencido(fila, hoy));

  return {
    saldoNeto,
    tone: resolveDeudaSaldoTone(saldoNeto, tieneVencidos),
    tieneVencidos,
    filas,
  };
}

export function deudaSaldoToneClassName(tone: DeudaSaldoTone): string {
  return `deudaSaldo--${tone}`;
}
