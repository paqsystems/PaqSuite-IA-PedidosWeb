export type PivotCoexistenceInput = {
  pivotsEnabled: boolean;
  pivotHabilitado: boolean;
  tipoProceso?: string | null;
  mostrarGrillaYPivot?: boolean;
};

export function canCoexistGridAndPivot(input: PivotCoexistenceInput): boolean {
  if (!input.pivotsEnabled || !input.pivotHabilitado) {
    return false;
  }

  const isInforme = (input.tipoProceso ?? '').trim().toLowerCase() === 'informe';

  return isInforme || input.mostrarGrillaYPivot === true;
}

export function shouldShowPivotOnly(input: PivotCoexistenceInput): boolean {
  return input.pivotsEnabled && input.pivotHabilitado && !canCoexistGridAndPivot(input);
}
